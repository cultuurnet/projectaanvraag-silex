<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
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
     * @var BlockProjectCommandHandler|\PHPUnit_Framework_MockObject_MockObject
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

        $this->cultureFeed = $this
            ->getMockBuilder('\CultureFeed')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cultureFeedTest = $this
            ->getMockBuilder('\CultureFeed')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->user = $this->getMock(User::class);
        $this->user->id = 123;

        $this->commandHandler = new BlockProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeed, $this->cultureFeedTest, $this->user);
    }

    /**
     * Test the command handler
     */
    public function testHandle()
    {

        $project = new Project();
        $project->setLiveConsumerKey('liveconsumerkey');
        $project->setTestConsumerKey('testconsumerkey');

        $liveConsumer = new \CultureFeed_Consumer();
        $liveConsumer->name = 'live';
        $liveConsumerBlocked = clone $liveConsumer;
        $liveConsumerBlocked->status = 'BLOCKED';
        $testConsumer = new \CultureFeed_Consumer();
        $testConsumer->name = 'test';
        $testConsumerBlocked = clone $testConsumer;
        $testConsumerBlocked->status = 'BLOCKED';

        $this->cultureFeed->expects($this->once())
            ->method('getServiceConsumer')
            ->with('liveconsumerkey')
            ->willReturn($liveConsumer);

        $this->cultureFeedTest->expects($this->once())
            ->method('getServiceConsumer')
            ->with('testconsumerkey')
            ->willReturn($testConsumer);

        // Test service updates.
        $this->cultureFeed
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->with($liveConsumerBlocked);
        $this->cultureFeedTest
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->with($testConsumerBlocked);

        // Test event.
        $projectBlocked = new ProjectBlocked($project);
        $this->eventBus
            ->expects($this->once())
            ->method('handle')
            ->with($projectBlocked);

        $blockProject = new BlockProject($project);
        $this->commandHandler->handle($blockProject);
    }

    /**
     * Test if it skips consumers with empty keys.
     */
    public function testIgnoreEmptyKeys()
    {

        $project = new Project();

        $this->cultureFeed->expects($this->never())
            ->method('getServiceConsumer');

        $this->cultureFeedTest->expects($this->never())
            ->method('getServiceConsumer');

        $blockProject = new BlockProject($project);
        $this->commandHandler->handle($blockProject);
    }
}
