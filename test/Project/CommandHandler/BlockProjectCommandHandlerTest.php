<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultureFeed;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class BlockProjectCommandHandlerTest extends TestCase
{
    /**
     * @var MessageBusSupportingMiddleware & MockObject
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface & MockObject
     */
    protected $entityManager;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeed;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeedTest;

    /**
     * @var BlockProjectCommandHandler & MockObject
     */
    protected $commandHandler;

    /**
     * @var UserInterface & MockObject
     */
    protected $user;

    public function setUp()
    {
        $this->eventBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->cultureFeed = $this->createMock(CultureFeed::class);

        $this->cultureFeedTest = $this->createMock('\CultureFeed');

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->user = new User();
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
        $liveConsumer->consumerKey = 'liveconsumerkey';
        $liveConsumer->status = 'BLOCKED';
        $testConsumer = new \CultureFeed_Consumer();
        $testConsumer->consumerKey = 'testconsumerkey';
        $testConsumer->status = 'BLOCKED';

        // Test service updates.
        $this->cultureFeed
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->with($liveConsumer);
        $this->cultureFeedTest
            ->expects($this->any())
            ->method('updateServiceConsumer')
            ->with($testConsumer);

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
