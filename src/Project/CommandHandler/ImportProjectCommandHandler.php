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

        $integrationTypeId = $importProject->getIntegrationType();
        $integrationType = $this->integrationTypeStorage->load($integrationTypeId);
        if (!$integrationType) {
            throw new \RuntimeException("Cannot import project for unknown integration type ({$integrationTypeId}).");
        }

        // Prepare project.
        $project = new Project();
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setGroupId($importProject->getIntegrationType());
        $project->setUserId($this->user->id);
        $project->setPlatformUuid($importProject->getPlatformUuid());

        $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());

        // Save the project to the local database.
        $this->entityManager->persist($project);

        // Create a local user if needed.
        $localUser = $this->entityManager->getRepository('ProjectAanvraag:User')->find($project->getUserId());
        if (empty($localUser)) {
            $newUser = new User($this->user->id);
            $this->entityManager->persist($newUser);
            $localUser = clone $newUser; // Cloning for unit tests.
        }

        $this->entityManager->flush();

        /**
         *  4. Add additional user info
         */
        $localUser->setFirstName($this->user->givenName);
        $localUser->setLastName($this->user->familyName);
        $localUser->setEmail($this->user->mbox);
        $localUser->setNick($this->user->nick);

        $projectImported = new ProjectImported($project, $localUser);
        $this->eventBus->handle($projectImported);

        $this->logger->debug('Finished handling ImportProject for ' . $importProject->getName());
    }

    /**
     * Create a user on test if that user does not exist yet.
     */
    private function createTestUser($nick, $email)
    {
        $searchQuery = new \CultureFeed_SearchUsersQuery();
        $searchQuery->mbox = $email;
        $searchQuery->mboxIncludePrivate = true;
        /** @var \CultureFeed_ResultSet $result */
        $result = $this->cultureFeedTest->searchUsers($searchQuery);

        // The user already exists?
        if ($result->total > 0) {
            return $result->objects[0]->id;
        }

        $user = new \CultureFeed_User();
        $user->mbox = $email;
        $user->nick = $nick;
        $user->password = $this->generatePassword();
        $user->status = \CultureFeed_User::STATUS_PRIVATE;

        return $this->cultureFeedTest->createUser($user);
    }
}
