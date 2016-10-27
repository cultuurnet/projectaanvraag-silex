<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateProjectCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MessageBusSupportingMiddleware|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \CultureFeed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cultureFeedTest;

    /**
     * @var CreateProjectCommandHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandHandler;

    /**
     * @var UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventBus = $this
            ->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cultureFeedTest = $this
            ->getMockBuilder('\CultureFeed')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventBus
            ->expects($this->any())
            ->method('handle');

        $this->entityManager
            ->expects($this->any())
            ->method('persist');

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->user = $this->getMock(User::class);
        $this->user->id = 123;

        $this->commandHandler = new CreateProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeedTest, $this->user);
    }

    /**
     * Test the command handler
     */
    public function testHandle()
    {
        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = new \CultureFeed_Consumer();
        $cultureFeedConsumer->name = 'Project name';
        $cultureFeedConsumer->description = 'Project description';
        $cultureFeedConsumer->consumerKey = 'cfe3ccae8aa248faddaedede30622177';
        $cultureFeedConsumer->consumerSecret = 'abc3ccae8aa248faddaedede30622177';

        $this->cultureFeedTest
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->will($this->returnValue($cultureFeedConsumer));

        $createProject = new CreateProject('project name', 'project description', 123);
        $this->commandHandler->handle($createProject);
    }

    /**
     * Test the command handler exception
     * @expectedException \CultureFeed_ParseException
     */
    public function testHandleException()
    {
        $this->cultureFeedTest
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->willThrowException(new \CultureFeed_ParseException('CultureFeed parse exception'));

        $createProject = new CreateProject('project name', 'project description', 123);
        $this->commandHandler->handle($createProject);
    }
}
