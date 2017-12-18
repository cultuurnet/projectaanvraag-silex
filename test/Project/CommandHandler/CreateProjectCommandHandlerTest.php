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

        $this->user = new User();
        $this->user->id = 123;
        $this->user->mbox = 'test@test.be';
        $this->user->nick = 'test';

        $this->commandHandler = $this->getMockBuilder(CreateProjectCommandHandler::class)
            ->setConstructorArgs([$this->eventBus, $this->entityManager, $this->cultureFeedTest, $this->cultureFeed, $this->user, 3])
            ->setMethods(['generatePassword'])
            ->getMock();
    }

    /**
     * Setup a handle test.
     */
    private function setupHandleTest($uid)
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
        $newUser = new \CultuurNet\ProjectAanvraag\Entity\User(123);
        $this->entityManager
            ->expects($this->at(4))
            ->method('persist')
            ->with($newUser);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Test creating of service consumers.
        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = new \CultureFeed_Consumer();
        $cultureFeedConsumer->name = 'Project name';
        $cultureFeedConsumer->description = 'Project description';
        $cultureFeedConsumer->group = [3, 123];

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $culturefeedTestconsumer = new \CultureFeed_Consumer();
        $culturefeedTestconsumer->name = 'Project name';
        $culturefeedTestconsumer->description = 'Project description';
        $culturefeedTestconsumer->consumerKey = 'test key';
        $culturefeedTestconsumer->consumerSecret = 'test secret';

        $culturefeedLiveConsumer = clone $culturefeedTestconsumer;
        $culturefeedLiveConsumer->consumerSecret = 'live secret';
        $culturefeedLiveConsumer->consumerKey = 'live key';

        // It should create a test consumer.
        $this->cultureFeedTest
            ->expects($this->once())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($culturefeedTestconsumer);

        // It should add user as admin.
        $this->cultureFeedTest
            ->expects($this->once())
            ->method('addServiceConsumerAdmin')
            ->with($culturefeedTestconsumer->consumerKey, $uid);

        // It should create a live consumer.
        $this->cultureFeed
            ->expects($this->once())
            ->method('createServiceConsumer')
            ->with($cultureFeedConsumer)
            ->willReturn($culturefeedLiveConsumer);

        // Test saving of the project.
        $project = new Project();
        $project->setName('Project name');
        $project->setDescription('Project description');
        $project->setGroupId(123);
        $project->setUserId($this->user->id);
        $project->setCoupon('coupon');
        $project->setStatus(Project::PROJECT_STATUS_ACTIVE);
        $project->setLiveConsumerKey($culturefeedLiveConsumer->consumerKey);
        $project->setTestConsumerKey($culturefeedTestconsumer->consumerKey);

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
    }

    /**
     * Test the command handler
     */
    public function testHandle()
    {

        $this->setupHandleTest('testuserid');

        // It should search for a user on test.
        $searchQuery = new \CultureFeed_SearchUsersQuery();
        $searchQuery->mbox = 'test@test.be';
        $searchQuery->mboxIncludePrivate = true;
        $result = new \CultureFeed_ResultSet();
        $result->total = 0;
        $this->cultureFeedTest
            ->expects($this->once())
            ->method('searchUsers')
            ->with($searchQuery)
            ->willReturn($result);

        // It should add a new test user.
        $this->commandHandler
            ->expects($this->once())
            ->method('generatePassword')
            ->willReturn('password');

        $newUser = new \CultureFeed_User();
        $newUser->mbox = 'test@test.be';
        $newUser->nick = 'test';
        $newUser->password = 'password';
        $newUser->status = \CultureFeed_User::STATUS_PRIVATE;
        $this->cultureFeedTest
            ->expects($this->once())
            ->method('createUser')
            ->with($newUser)
            ->willReturn('testuserid');

        $createProject = new CreateProject('Project name', 'Project description', 123, 'coupon');
        $this->commandHandler->handle($createProject);
    }

    /**
     * Test the command handler when test user does not exist yet.
     */
    public function testHandleNewTestUser()
    {

        $this->setupHandleTest(20);

        $searchQuery = new \CultureFeed_SearchUsersQuery();
        $searchQuery->mbox = 'test@test.be';
        $searchQuery->mboxIncludePrivate = true;
        $result = new \CultureFeed_ResultSet();
        $user = new \stdClass();
        $user->id = '20';
        $result->total = 1;
        $result->objects = [
            $user,
        ];

        // It should find a user and never create a user.
        $this->cultureFeedTest
            ->expects($this->once())
            ->method('searchUsers')
            ->with($searchQuery)
            ->willReturn($result);

        $this->cultureFeedTest
            ->expects($this->never())
            ->method('createUser');

        $createProject = new CreateProject('Project name', 'Project description', 123, 'coupon');
        $this->commandHandler->handle($createProject);
    }
}
