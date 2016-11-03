<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class BlockProjectCommandHandler
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \CultureFeed
     */
    protected $cultureFeedTest;

    /**
     * @var \CultureFeed
     */
    protected $cultureFeed;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * BlockProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \ICultureFeed $cultureFeed
     * @param \ICultureFeed $cultureFeedTest
     * @param User $user
     */
    public function __construct(
        MessageBusSupportingMiddleware $eventBus,
        EntityManagerInterface $entityManager,
        \ICultureFeed $cultureFeed,
        \ICultureFeed $cultureFeedTest,
        User $user
    ) {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->cultureFeed = $cultureFeed;
        $this->cultureFeedTest = $cultureFeedTest;
        $this->user = $user;
    }

    /**
     * Handle the command
     * @param BlockProject $blockProject
     * @throws \Exception
     */
    public function handle(BlockProject $blockProject)
    {
        /** @var ProjectInterface $project */
        $project = $blockProject->getProject();

        // 1. Block the live consumer
        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $consumer = new \CultureFeed_Consumer();
        $consumer->status = 'BLOCKED';
        $consumer->name = $project->getName();

        $consumer->consumerKey = $project->getLiveConsumerKey();
        $this->cultureFeed->updateServiceConsumer($consumer);

        // 2. Block the test consumer
        $consumer->consumerKey = $project->getTestConsumerKey();
        $this->cultureFeedTest->updateServiceConsumer($consumer);

        // 3. Update the project status
        $project->setStatus(ProjectInterface::PROJECT_STATUS_BLOCKED);
        $this->entityManager->flush();

        // 4. Dispatch ProjectUpdated event
        $projectDeleted = new ProjectDeleted($project);
        $this->eventBus->handle($projectDeleted);
    }
}
