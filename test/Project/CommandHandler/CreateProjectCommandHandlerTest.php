<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
    protected $cultureFeed;

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

        $this->cultureFeed = $this
            ->getMockBuilder('\CultureFeed')
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventBus
            ->expects($this->any())
            ->method('handle');

        $this->user = $this->getMock(User::class);
        $this->user->id = 123;

        $this->commandHandler = new CreateProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeedTest, $this->cultureFeed, $this->user);
    }

    /**
     * Test the command handler
     */
    public function testHandle()
    {
        $repository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('ProjectAanvraag:User')
            ->willReturn($repository);

        $this->entityManager
            ->expects($this->any())
            ->method('find')
            ->with($this->user->id);

        $this->entityManager
            ->expects($this->any())
            ->method('persist');

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = new \CultureFeed_Consumer();
        $cultureFeedConsumer->name = 'Project name';
        $cultureFeedConsumer->description = 'Project description';
        $cultureFeedConsumer->group = [5, 123];

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $createdCultureFeedConsumer = new \CultureFeed_Consumer();
        $createdCultureFeedConsumer->name = 'Project name';
        $createdCultureFeedConsumer->description = 'Project description';
        $createdCultureFeedConsumer->consumerKey = 'cfe3ccae8aa248faddaedede30622177';
        $createdCultureFeedConsumer->consumerSecret = 'abc3ccae8aa248faddaedede30622177';

        $this->cultureFeedTest
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($createdCultureFeedConsumer);

        $this->cultureFeed
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($createdCultureFeedConsumer);

        $createProject = new CreateProject('Project name', 'Project description', 123, 'coupon');
        $this->commandHandler->handle($createProject);
    }
}
