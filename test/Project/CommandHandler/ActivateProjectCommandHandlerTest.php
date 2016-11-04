<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var BlockProjectCommandHandler|\PHPUnit_Framework_MockObject_MockObject
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

        $this->commandHandler = new ActivateProjectCommandHandler($this->eventBus, $this->entityManager, $this->cultureFeed, $this->user);
    }

    /**
     * Test the command handler
     */
    public function testActivationWithCoupon()
    {

        $consumer = new \CultureFeed_Consumer();
        $consumer->name = $this->project->getName();
        $consumer->description = $this->project->getDescription();
        $consumer->group = [5, $this->project->getGroupId()];

        $consumerWithId = clone $consumer;
        $consumerWithId->consumerKey = 'test';

        // Test saving to culturefeed live.
        $this->project->expects($this->once())
            ->method('setStatus')
            ->with(ProjectInterface::PROJECT_STATUS_ACTIVE);
        $this->project->expects($this->once())
            ->method('setLiveConsumerKey')
            ->with($consumerWithId->consumerKey);

        $this->cultureFeed
            ->expects($this->once())
            ->method('createServiceConsumer')
            ->with($consumer)
            ->willReturn($consumerWithId);

        // Test saving to db.
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->project);

        // Test dispatching of event.
        $projectActivated = new ProjectActivated($this->project);
        $this->eventBus->expects($this->once())
            ->method('handle')
            ->with($projectActivated);

        $this->commandHandler->handle(new ActivateProject($this->project));
    }
}
