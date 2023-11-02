<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectImported;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class ImportProjectCommandHandler
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    private $eventBus;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MessageBusSupportingMiddleware $eventBus,
        EntityManagerInterface $entityManager,
        UserInterface $user,
        LoggerInterface $logger
    ) {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->logger = $logger;
    }

    public function handle(ImportProject $importProject): void
    {
        $this->logger->debug('Start handling ImportProject for ' . $importProject->getName());

        $project = new Project();
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setGroupId($importProject->getGroupId());
        $project->setUserId($this->user->id);
        $project->setPlatformUuid($importProject->getPlatformUuid());
        $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        $this->entityManager->persist($project);

        $localUser = $this->entityManager->getRepository('ProjectAanvraag:User')->find($project->getUserId());
        if (empty($localUser)) {
            $newUser = new User($this->user->id);
            $this->entityManager->persist($newUser);
            $localUser = clone $newUser; // Cloning for unit tests.
        }

        $this->entityManager->flush();

        $localUser->setFirstName($this->user->givenName);
        $localUser->setLastName($this->user->familyName);
        $localUser->setEmail($this->user->mbox);
        $localUser->setNick($this->user->nick);

        $projectImported = new ProjectImported($project, $localUser);
        $this->eventBus->handle($projectImported);

        $this->logger->debug('Finished handling ImportProject for ' . $importProject->getName());
    }
}
