<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\PasswordGeneratorTrait;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\User\UserInterface as UitIdUserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateProjectCommandHandler
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
     * @param CreateProject $createProject
     * @throws \Throwable
     */
    public function handle(CreateProject $createProject)
    {
        $integrationTypeId = $createProject->getIntegrationType();
        $integrationType = $this->integrationTypeStorage->load($integrationTypeId);
        if (!$integrationType) {
            throw new \RuntimeException("Cannot create project for unknown integration type ({$integrationTypeId}).");
        }

        // Prepare project.
        $project = new Project();
        $project->setName($createProject->getName());
        $project->setDescription($createProject->getDescription());
        $project->setGroupId($createProject->getIntegrationType());
        $project->setUserId($this->user->id);

        $project->setCoupon($createProject->getCouponToUse());
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        // Create the test consumer.
        $testConsumer = $this->createTestConsumer($createProject, $integrationType);

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $project->setTestConsumerKey($testConsumer->consumerKey);
        $project->setTestApiKeySapi3($testConsumer->apiKeySapi3);

        // Create a live service consumer when a coupon is provided.
        if (!empty($createProject->getCouponToUse())) {
            /** @var \CultureFeed_Consumer $cultureFeedConsumer */
            $createConsumer = new \CultureFeed_Consumer();
            $createConsumer->name = $createProject->getName();
            $createConsumer->description = $createProject->getDescription();
            $createConsumer->group = $integrationType->getUitIdPermissionGroups();
            $cultureFeedLiveConsumer = $this->cultureFeed->createServiceConsumer($createConsumer);
            // Add uitpas permission to consumer
            $this->cultureFeed->addUitpasPermission($cultureFeedLiveConsumer, $integrationType->getUitPasPermissionGroups());
            $project->setStatus(Project::PROJECT_STATUS_ACTIVE);
            $project->setLiveConsumerKey($cultureFeedLiveConsumer->consumerKey);
            $project->setLiveApiKeySapi3($cultureFeedLiveConsumer->apiKeySapi3);
        }

        // Save the project to the local database.
        $this->entityManager->persist($project);

        // Mark coupon as used.
        if ($createProject->getCouponToUse()) {
            /** @var Coupon $coupon */
            $coupon = $this->entityManager->getRepository('ProjectAanvraag:Coupon')->find($createProject->getCouponToUse());
            $coupon->setUsed(true);
            $this->entityManager->persist($coupon);
        }

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

        /**
         * 5. Dispatch the ProjectCreated event
         */
        $projectCreated = new ProjectCreated($project, $localUser, $createProject->getCouponToUse());
        $this->eventBus->handle($projectCreated);
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

    /**
     * Create the test consumer, and add the user as admin.
     * @param CreateProject $createProject
     */
    private function createTestConsumer(CreateProject $createProject, IntegrationType $integrationType)
    {
        // Make sure the user also exists on test.
        $uid = $this->createTestUser($this->user->getUsername(), $this->user->mbox);

        // Create test consumer.
        $createConsumer = new \CultureFeed_Consumer();
        $createConsumer->name = $createProject->getName();
        $createConsumer->description = $createProject->getDescription();
        $createConsumer->group = $integrationType->getUitIdPermissionGroups();
        $cultureFeedConsumer = $this->cultureFeedTest->createServiceConsumer($createConsumer);

        // Add the user as service consumer admin.
        $this->cultureFeedTest->addServiceConsumerAdmin($cultureFeedConsumer->consumerKey, $uid);

        // Add uitpas permission to consumer
        $this->cultureFeedTest->addUitpasPermission($cultureFeedConsumer, $integrationType->getUitPasPermissionGroups());

        return $cultureFeedConsumer;
    }
}
