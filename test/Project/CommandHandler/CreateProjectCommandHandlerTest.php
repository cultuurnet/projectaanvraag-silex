<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\Project;
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
            ->expects($this->at(3))
            ->method('getRepository')
            ->with('ProjectAanvraag:User')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('find')
            ->with($this->user->id);

        // Test saving of the user.
        $user = new \CultuurNet\ProjectAanvraag\Entity\User(123);
        $this->entityManager
            ->expects($this->at(4))
            ->method('persist')
            ->with($user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Test creating of service consumers.
        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = new \CultureFeed_Consumer();
        $cultureFeedConsumer->name = 'Project name';
        $cultureFeedConsumer->description = 'Project description';
        $cultureFeedConsumer->group = [5, 123];

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $createdCultureFeedConsumer = new \CultureFeed_Consumer();
        $createdCultureFeedConsumer->name = 'Project name';
        $createdCultureFeedConsumer->description = 'Project description';
        $createdCultureFeedConsumer->consumerKey = 'test key';
        $createdCultureFeedConsumer->consumerSecret = 'test secret';
        $createLiveConsumer = clone $createdCultureFeedConsumer;
        $createLiveConsumer->consumerSecret = 'live secret';
        $createLiveConsumer->consumerKey = 'live key';

        $this->cultureFeedTest
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($createdCultureFeedConsumer);

        $this->cultureFeed
            ->expects($this->any())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($createLiveConsumer);

        // Test saving of the project.
        $project = new Project();
        $project->setName('Project name');
        $project->setDescription('Project description');
        $project->setGroupId(123);
        $project->setUserId($this->user->id);
        $project->setCoupon('coupon');
        $project->setStatus(Project::PROJECT_STATUS_ACTIVE);
        $project->setLiveConsumerKey($createLiveConsumer->consumerKey);
        $project->setTestConsumerKey($createdCultureFeedConsumer->consumerKey);

        $this->entityManager->expects($this->at(0))
            ->method('persist')
            ->with($project);

        // Test coupon saving.
        $coupon = new Coupon();
        $savedCoupon = clone $coupon;
        $savedCoupon->setUsed(true);
        $couponRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager
            ->expects($this->at(1))
            ->method('getRepository')
            ->with('ProjectAanvraag:Coupon')
            ->willReturn($couponRepository);
        $couponRepository->expects($this->once())
            ->method('find')
            ->with('coupon')
            ->willReturn($coupon);

        $this->entityManager->expects($this->at(2))
            ->method('persist')
            ->with($savedCoupon);

        $createProject = new CreateProject('Project name', 'Project description', 123, 'coupon');
        $this->commandHandler->handle($createProject);
    }
}
