<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    /** @var  EntityRepository|\PHPUnit_Framework_MockObject_MockObject */
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
        $this->projectRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->integrationTypeStorage = $this->getMock(IntegrationTypeStorageInterface::class);
        $this->user = $this->getMock(User::class);
        $this->user->id = 'id';


        $entityManager = $this->getMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($this->projectRepository);
        $this->projectService = new ProjectService($this->culturefeedLive, $this->culturefeedTest, $entityManager, $this->integrationTypeStorage, $this->user);
    }

    /**
     * Test if projects can be searched by name
     */
    public function testSearchrojectsByName()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        // Add limits.
        $criteria->setFirstResult(0)
            ->setMaxResults(20);

        $criteria->where($expr->eq('p.userId', $this->user->id));
        $criteria->andWhere($expr->contains('p.name', 'test'));

        $this->searchTest($criteria, 0, 20, 'test');
    }

    /**
     * Test if projects can be loaded with pagination for a non admin.
     */
    public function testSearchrojects()
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        // Add limits.
        $criteria->setFirstResult(10)
            ->setMaxResults(50);

        $criteria->where($expr->eq('p.userId', $this->user->id));

        $this->searchTest($criteria, 10, 50);
    }

    /**
     * Helper method to test the search.
     */
    private function searchTest($criteria, $start, $max, $name = '')
    {
        // Mock the querybuilder.
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Return the mock on createQueryBuilder.
        $this->projectRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('p')
            ->willReturn($queryBuilder);

        // Check if criteria matches.
        $queryBuilder->expects($this->at(0))
            ->method('addCriteria')
            ->with($criteria)
            ->willReturn('list');

        // Mock a query object.
        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getResult', 'getSingleScalarResult'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $query->expects($this->at(0))
            ->method('getResult')
            ->willReturn('list');

        $query->expects($this->at(1))
            ->method('getSingleScalarResult')
            ->willReturn(20);

        // Correctly return the mocked query.
        $queryBuilder->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);

        $queryBuilder->expects($this->any())
            ->method('select')
            ->with('count(p.id)')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->any())
            ->method('setFirstResult')
            ->with(0)
            ->willReturn($queryBuilder);

        $this->assertEquals(
            [
                'total' => 20,
                'results' => 'list',
            ],
            $this->projectService->searchProjects($start, $max, $name)
        );

    }

    /**
     * Test if projects can be loaded for an admin.
     */
    public function testLoadProjectsForAdmins()
    {
        $this->user->expects($this->any())->method('isAdmin')
            ->willReturn(true);

        $criteria = Criteria::create();

        // Add limits.
        $criteria->setFirstResult(0)
            ->setMaxResults(20);

        $this->searchTest($criteria, 0, 20);
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
        /** @var Project|\PHPUnit_Framework_MockObject_MockObject $project */
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
        /** @var Project|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->getMock(Project::class, ['enrichWithConsumerInfo']);

        $project->setName('name');
        $project->setTestConsumerKey('test');
        $project->setGroupId('test');

        $this->projectRepository
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
        /** @var Project|\PHPUnit_Framework_MockObject_MockObject $project */
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
    public function testLoadProjectNotFound()
    {
        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1])
            ->willReturn(null);

        $this->assertNull($this->projectService->loadProject(1));
    }
}
