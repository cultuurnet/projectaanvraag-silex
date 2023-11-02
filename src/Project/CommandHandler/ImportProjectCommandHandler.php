<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\PasswordGeneratorTrait;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectImported;
use CultuurNet\ProjectAanvraag\User\UserInterface as UitIdUserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class ImportProjectCommandHandler
{

    use PasswordGeneratorTrait;

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var \ICultureFeed
     */
    protected $cultureFeedTest;

    /**
     * @var \ICultureFeed
     */
    protected $cultureFeed;

    /**
     * @var UitIdUserInterface
     */
    protected $user;

    /**
     * @var IntegrationTypeStorageInterface
     */
    private $integrationTypeStorage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MessageBusSupportingMiddleware $eventBus,
        EntityManagerInterface $entityManager,
        \ICultureFeed $cultureFeedTest,
        \ICultureFeed $cultureFeed,
        UitIdUserInterface $user,
        IntegrationTypeStorageInterface $integrationTypeStorage,
        LoggerInterface $logger
    ) {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->cultureFeedTest = $cultureFeedTest;
        $this->cultureFeed = $cultureFeed;
        $this->user = $user;
        $this->integrationTypeStorage = $integrationTypeStorage;
        $this->logger = $logger;
    }

    /**
     * Handle the command
     * @param ImportProject $importProject
     * @throws \Throwable
     */
    public function handle(ImportProject $importProject)
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
