<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class DeleteProjectCommandHandler
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
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeed
     * @param \CultureFeed $cultureFeedTest
     * @param User $user
     */
    public function __construct(
        MessageBusSupportingMiddleware $eventBus,
        EntityManagerInterface $entityManager,
        \CultureFeed $cultureFeed,
        \CultureFeed $cultureFeedTest,
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
     * @param DeleteProject $deleteProject
     * @throws \Exception
     */
    public function handle(DeleteProject $deleteProject)
    {
        // 1. Block the live consumer
        // 2. Block the test consumer
        // 3. Dispatch ProjectDeleted event
        // 3. Dispatch the ProjectCreated event
        $projectDeleted = new ProjectDeleted($deleteProject->getProject());
        $this->eventBus->handle($projectDeleted);
    }
}
