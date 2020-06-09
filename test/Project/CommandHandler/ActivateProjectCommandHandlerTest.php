<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class ActivateProjectCommandHandlerTest extends \PHPUnit_Framework_TestCase
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

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->project = $this->getMock(ProjectInterface::class);

        $this->user = $this->getMock(User::class);
        $this->user->id = 123;
        $this->permissionGroups = [
          'default_consumer' => 3,
          'uitpas' => 22678,
          'auth0_refresh_token' => 24640,
          'entry_v3' => 24380
        ];

        $this->commandHandler = new ActivateProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeed, $this->user, $this->permissionGroups);
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
        $couponRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

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
