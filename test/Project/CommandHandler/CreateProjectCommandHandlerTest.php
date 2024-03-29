<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultureFeed;
use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateProjectCommandHandlerTest extends TestCase
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
     * @var CreateProjectCommandHandler & MockObject
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

        $this->cultureFeedTest = $this->createMock(CultureFeed::class);

        $this->cultureFeed = $this->createMock(CultureFeed::class);

        $this->eventBus
            ->expects($this->any())
            ->method('handle');

        $integrationType = new IntegrationType();
        $integrationType->setUitIdPermissionGroups([3, 123]);
        $integrationType->setUitPasPermissionGroups([]);

        $integrationTypeStorage = $this->createMock(IntegrationTypeStorageInterface::class);
        $integrationTypeStorage
            ->method('load')
            ->with(123)
            ->willReturn($integrationType);

        $this->user = new User();
        $this->user->id = 123;
        $this->user->mbox = 'test@test.be';
        $this->user->nick = 'test';

        $this->commandHandler = $this->getMockBuilder(CreateProjectCommandHandler::class)
            ->setConstructorArgs(
                [
                    $this->eventBus,
                    $this->entityManager,
                    $this->cultureFeedTest,
                    $this->cultureFeed,
                    $this->user,
                    $integrationTypeStorage,
                    $this->createMock(LoggerInterface::class),
                ]
            )
            ->setMethods(['generatePassword'])
            ->getMock();
    }

    /**
     * Setup a handle test.
     */
    private function setupHandleTest($uid)
    {

        $repository = $this->createMock(EntityRepository::class);

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
        $culturefeedTestconsumer->apiKeySapi3 = 'search test key';

        $culturefeedLiveConsumer = clone $culturefeedTestconsumer;
        $culturefeedLiveConsumer->consumerSecret = 'live secret';
        $culturefeedLiveConsumer->consumerKey = 'live key';
        $culturefeedLiveConsumer->apiKeySapi3 = 'search live key';

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
        $project->setLiveApiKeySapi3($culturefeedLiveConsumer->apiKeySapi3);
        $project->setTestApiKeySapi3($culturefeedTestconsumer->apiKeySapi3);

        $this->entityManager->expects($this->at(0))
            ->method('persist')
            ->with($project);

        // Test coupon saving.
        $coupon = new Coupon();
        $savedCoupon = clone $coupon;
        $savedCoupon->setUsed(true);
        $couponRepository = $this->createMock(EntityRepository::class);
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
