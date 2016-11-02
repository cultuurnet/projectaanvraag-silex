<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class BlockProjectCommandHandlerTest extends \PHPUnit_Framework_TestCase
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
    protected $cultureFeed;

    /**
     * @var \CultureFeed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cultureFeedTest;

    /**
     * @var DeleteProjectCommandHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandHandler;

    /**
     * @var UserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $user;

    /**
     * @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

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

        $this->cultureFeed = $this
            ->getMockBuilder('\CultureFeed')
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
            ->method('flush');

        $this->project = $this->getMock(ProjectInterface::class);

        $this->user = $this->getMock(User::class);
        $this->user->id = 123;

        $this->commandHandler = new BlockProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeed, $this->cultureFeedTest, $this->user);
    }

    /**
     * Test the command handler
     */
    public function testHandle()
    {
        $consumer = $this->getMock(\CultureFeed_Consumer::class);

        $this->cultureFeedTest
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->will($this->returnValue($consumer));

        $this->cultureFeed
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->will($this->returnValue($consumer));

        $blockProject = new BlockProject($this->project);
        $this->commandHandler->handle($blockProject);
    }

    /**
     * Test the command handler exception
     * @expectedException \CultureFeed_ParseException
     */
    public function testHandleException()
    {
        $consumer = $this->getMock(\CultureFeed_Consumer::class);

        $this->cultureFeedTest
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->willThrowException(new \CultureFeed_ParseException('CultureFeed parse exception'));

        $this->cultureFeed
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->will($this->returnValue($consumer));

        $blockProject = new BlockProject($this->project);
        $this->commandHandler->handle($blockProject);
    }
}
