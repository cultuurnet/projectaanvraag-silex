<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
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

    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
    }

    public function handle(CreateProject $createProject)
    {
        $project = new Project();
        $project->setName($createProject->getName());
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);
        $project->setTestConsumerKey('consumer-key');

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        /*$projectCreated = new ProjectCreated(1);
        $this->eventBus->handle($projectCreated);*/
    }
}
