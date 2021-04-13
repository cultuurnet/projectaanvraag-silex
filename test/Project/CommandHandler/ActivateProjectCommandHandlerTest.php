<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultureFeed;
use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class ActivateProjectCommandHandlerTest extends TestCase
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
     * @var ActivateProjectCommandHandler|\PHPUnit_Framework_MockObject_MockObject
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
        $this->eventBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->cultureFeed = $this->createMock(CultureFeed::class);

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->project = $this->createMock(ProjectInterface::class);

        $this->project
            ->method('getGroupId')
            ->willReturn(123);

        $integrationType = new IntegrationType();
        $integrationType->setUitIdPermissionGroups([3, 123]);
        $integrationType->setUitPasPermissionGroups([]);

        $integrationTypeStorage = $this->createMock(IntegrationTypeStorageInterface::class);
        $integrationTypeStorage
            ->method('load')
            ->with(123)
            ->willReturn($integrationType);

        $this->user = $this->createMock(User::class);
        $this->user->id = 123;

        $this->commandHandler = new ActivateProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeed, $this->user, $integrationTypeStorage);
    }

    /**
     * Test the command handler
     */
    public function testActivationWithCoupon()
    {

        $consumer = new \CultureFeed_Consumer();
        $consumer->name = $this->project->getName();
        $consumer->description = $this->project->getDescription();
        $consumer->group = [3, $this->project->getGroupId()];

        $consumerWithId = clone $consumer;
        $consumerWithId->consumerKey = 'test';
        $consumerWithId->apiKeySapi3 = 'testkey';

        // Test saving to culturefeed live.
        $this->project->expects($this->once())
            ->method('setStatus')
            ->with(ProjectInterface::PROJECT_STATUS_ACTIVE);
        $this->project->expects($this->once())
            ->method('setLiveConsumerKey')
            ->with($consumerWithId->consumerKey);
        $this->project->expects($this->once())
            ->method('setLiveApiKeySapi3')
            ->with('testkey');

        $this->cultureFeed
            ->expects($this->once())
            ->method('createServiceConsumer')
            ->with($consumer)
            ->willReturn($consumerWithId);

        // Test saving to db.
        $this->entityManager->expects($this->at(0))
            ->method('persist')
            ->with($this->project);

        // Coupon saving.
        $coupon = new Coupon();
        $savedCoupon = clone $coupon;
        $savedCoupon->setUsed(true);
        $couponRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->any())
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

        // Test dispatching of event.
        $projectActivated = new ProjectActivated($this->project, 'coupon');
        $this->eventBus->expects($this->once())
            ->method('handle')
            ->with($projectActivated);

        $this->commandHandler->handle(new ActivateProject($this->project, 'coupon'));
    }
}
