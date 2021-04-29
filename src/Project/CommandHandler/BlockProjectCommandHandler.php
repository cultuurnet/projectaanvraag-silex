<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
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
        if ($project->getLiveConsumerKey()) {
            $consumer = new \CultureFeed_Consumer();
            $consumer->consumerKey = $project->getLiveConsumerKey();
            $consumer->status = 'BLOCKED';
            $this->cultureFeed->updateServiceConsumer($consumer);
        }

        // 2. Block the test consumer
        if ($project->getTestConsumerKey()) {
            $consumer = new \CultureFeed_Consumer();
            $consumer->consumerKey = $project->getTestConsumerKey();
            $consumer->status = 'BLOCKED';
            $this->cultureFeedTest->updateServiceConsumer($consumer);
        }

        // 3. Update the project status
        $project->setStatus(ProjectInterface::PROJECT_STATUS_BLOCKED);
        $this->entityManager->flush();

        // 4. Dispatch ProjectBlocked event
        $projectBlocked = new ProjectBlocked($project);
        $this->eventBus->handle($projectBlocked);
    }
}
