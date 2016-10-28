<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tests the ProjectService class.
 */
class ProjectServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ProjectService */
    protected $projectService;

    /** @var  \ICultureFeed|\PHPUnit_Framework_MockObject_MockObject */
    protected $culturefeedLive;

    /** @var  \ICultureFeed|\PHPUnit_Framework_MockObject_MockObject */
    protected $culturefeedTest;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $projectRepository;

    /** @var  IntegrationTypeStorageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $integrationTypeStorage;

    /** @var  User|\PHPUnit_Framework_MockObject_MockObject */
    protected $user;

    /**
     * Setup the service with mock objects.
     */
    public function setUp()
    {
        $this->culturefeedLive = $this->getMock(\ICultureFeed::class);
        $this->culturefeedTest = $this->getMock(\ICultureFeed::class);
        $this->projectRepository = $this->getMock(ObjectRepository::class);
        $this->integrationTypeStorage = $this->getMock(IntegrationTypeStorageInterface::class);
        $this->user = $this->getMock(User::class);
        $this->user->id = 'id';
        $entityManager = $this->getMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($this->projectRepository);
        $this->projectService = new ProjectService($this->culturefeedLive, $this->culturefeedTest, $entityManager, $this->integrationTypeStorage, $this->user);
    }

    /**
     * Test if projects can be loaded with pagination for a non admin.
     */
    public function testLoadProjects()
    {
        $this->projectRepository->expects($this->at(0))
            ->method('findBy')
            ->with(['userId' => 'id'], ['created' => 'DESC'], 20, 0)
            ->willReturn('result');

        $this->assertEquals('result', $this->projectService->loadProjects());

        $this->projectRepository->expects($this->once())
            ->method('findBy')
            ->with(['userId' => 'id'], ['created' => 'DESC'], 10, 50)
            ->willReturn('result');
        $this->projectService->loadProjects(10, 50);
    }

    /**
     * Test if projects can be loaded for an admin.
     */
    public function testLoadProjectsForAdmins()
    {
        $this->user->expects($this->any())->method('isAdmin')
            ->willReturn(true);

        $this->projectRepository->expects($this->once())
            ->method('findBy')
            ->with([], ['created' => 'DESC'], 20, 0)
            ->willReturn('result');

        $this->assertEquals('result', $this->projectService->loadProjects());
    }

    /**
     * Test if the project is loaded with basic info.
     */
    public function testLoadProject()
    {
        $project = new Project();
        $project->setName('name');

        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($project);

        $this->assertEquals($project, $this->projectService->loadProject(1), 'It loads the project');
    }

    /**
     * Test if the project is loaded with enriched info.
     */
    public function testLoadProjectWithEnrichment()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->getMock(Project::class, ['enrichWithConsumerInfo']);
        $integrationType = $this->getMock(IntegrationType::class);

        $project->setName('name');
        $project->setLiveConsumerKey('live');
        $project->setTestConsumerKey('test');
        $project->setGroupId('test');

        $liveConsumer = $this->getMock(\CultureFeed_Consumer::class);
        $liveConsumer->consumerSecret = 'livesecret';
        $testConsumer = $this->getMock(\CultureFeed_Consumer::class);
        $testConsumer->consumerSecret = 'testsecret';

        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($project);

        $this->culturefeedLive->expects($this->once())
            ->method('getServiceConsumer')
            ->with('live')
            ->willReturn($liveConsumer);

        $this->culturefeedTest->expects($this->once())
            ->method('getServiceConsumer')
            ->with('test')
            ->willReturn($testConsumer);

        $this->integrationTypeStorage->expects($this->once())
            ->method('load')
            ->willReturn($integrationType);

        $project->expects($this->at(0))
            ->method('enrichWithConsumerInfo')
            ->with($testConsumer);

        $project->expects($this->at(1))
            ->method('enrichWithConsumerInfo')
            ->with($liveConsumer);


        $this->assertEquals($project, $this->projectService->loadProject(1), 'It loads the project with extra info');
    }

    /**
     * Test if test exceptions are handled.
     */
    public function testTestExceptions()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->getMock(Project::class, ['enrichWithConsumerInfo']);

        $project->setName('name');
        $project->setTestConsumerKey('test');
        $project->setGroupId('test');

        $this->projectRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($project);

        $this->culturefeedTest->expects($this->any())
            ->method('getServiceConsumer')
            ->with('test')
            ->willThrowException(new \CultureFeed_HttpException('test', 200));

        // No exception should be rethrown.
        $this->projectService->loadProject(1);

        $this->setExpectedException('\InvalidArgumentException', 'test');
        $this->culturefeedTest->expects($this->any())
            ->method('getServiceConsumer')
            ->with('test')
            ->willThrowException(new \InvalidArgumentException('test'));

        $this->projectService->loadProject(1);
    }

    /**
     * Test if live exceptions are handled.
     */
    public function testLiveExceptions()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->getMock(Project::class, ['enrichWithConsumerInfo']);

        $project->setName('name');
        $project->setLiveConsumerKey('live');

        $this->projectRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn($project);

        $this->culturefeedLive->expects($this->any())
            ->method('getServiceConsumer')
            ->with('live')
            ->willThrowException(new \CultureFeed_HttpException('test', 200));

        // No exception should be rethrown.
        $this->projectService->loadProject(1);

        $this->setExpectedException('\InvalidArgumentException', 'live');

        $this->culturefeedLive->expects($this->any())
            ->method('getServiceConsumer')
            ->with('live')
            ->willThrowException(new \InvalidArgumentException('live'));

        $this->projectService->loadProject(1);
    }

    /**
     * Test if NULL is returned when no project was found.
     */
    public function testLoadProjectNoAccess()
    {
        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $this->assertNull($this->projectService->loadProject(1));
    }
}
