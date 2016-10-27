<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateProjectCommandHandler
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
     * @var UserInterface
     */
    protected $user;

    /**
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeedTest
     * @param User $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, \CultureFeed $cultureFeedTest, User $user)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->cultureFeedTest = $cultureFeedTest;
        $this->user = $user;
    }

    /**
     * Handle the command
     * @param CreateProject $createProject
     * @throws \Throwable
     */
    public function handle(CreateProject $createProject)
    {
        // 1. Create a test consumer
        $createConsumer = new \CultureFeed_Consumer();
        $createConsumer->name = $createProject->getName();
        $createConsumer->description = $createProject->getDescription();
        $createConsumer->group = [5, $createProject->getIntegrationType()];

        try {
            // Try the service call
            /** @var \CultureFeed_Consumer $cultureFeedConsumer */
            $cultureFeedConsumer = $this->cultureFeedTest->createServiceConsumer($createConsumer);
        } catch (\Exception $e) {
            throw $e;
        }

        // 2. Save the project to the local database
        $project = new Project();
        $project->setName($cultureFeedConsumer->name);
        $project->setDescription($cultureFeedConsumer->description);
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);
        $project->setTestConsumerKey($cultureFeedConsumer->consumerKey);
        $project->setTestConsumerSecret($cultureFeedConsumer->consumerSecret);
        $project->setGroupId($createProject->getIntegrationType());
        $project->setUserId($this->user->id);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        // 3. Dispatch the ProjectCreated event
        $projectCreated = new ProjectCreated($project->getId());
        $this->eventBus->handle($projectCreated);
    }
}
