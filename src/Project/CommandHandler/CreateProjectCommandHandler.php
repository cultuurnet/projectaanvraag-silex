<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Coupon;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\User\UserInterface as UitIdUserInterface;
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
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \ICultureFeed $cultureFeedTest
     * @param \ICultureFeed $cultureFeed
     * @param UitIdUserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, \ICultureFeed $cultureFeedTest, \ICultureFeed $cultureFeed, UitIdUserInterface $user)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->cultureFeedTest = $cultureFeedTest;
        $this->cultureFeed = $cultureFeed;
        $this->user = $user;
    }

    /**
     * Handle the command
     * @param CreateProject $createProject
     * @throws \Throwable
     */
    public function handle(CreateProject $createProject)
    {
        /**
         * 1. Prepare project
         */
        $project = new Project();
        $project->setName($createProject->getName());
        $project->setDescription($createProject->getDescription());
        $project->setGroupId($createProject->getIntegrationType());
        $project->setUserId($this->user->id);
        $project->setCoupon($createProject->getCouponToUse());
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        /**
         * 2. Create a test service consumer
         */
        $createConsumer = new \CultureFeed_Consumer();
        $createConsumer->name = $createProject->getName();
        $createConsumer->description = $createProject->getDescription();
        $createConsumer->group = [5, $createProject->getIntegrationType()];

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = $this->cultureFeedTest->createServiceConsumer($createConsumer);
        $project->setTestConsumerKey($cultureFeedConsumer->consumerKey);

        // Create a live service consumer when a coupon is provided
        if (!empty($createProject->getCouponToUse())) {
            /** @var \CultureFeed_Consumer $cultureFeedConsumer */
            $cultureFeedLiveConsumer = $this->cultureFeed->createServiceConsumer($createConsumer);
            $project->setStatus(Project::PROJECT_STATUS_ACTIVE);
            $project->setLiveConsumerKey($cultureFeedLiveConsumer->consumerKey);
        }

        /**
         * 3. Save the project to the local database
         */
        $this->entityManager->persist($project);

        // Mark coupon as used.
        if ($createProject->getCouponToUse()) {
            /** @var Coupon $coupon */
            $coupon = $this->entityManager->getRepository('ProjectAanvraag:Coupon')->find($createProject->getCouponToUse());
            $coupon->setUsed(true);
            $this->entityManager->persist($coupon);
        }

        /**
         * 4. Create a local user if needed
         */
        $localUser = $this->entityManager->getRepository('ProjectAanvraag:User')->find($project->getUserId());
        if (empty($localUser)) {
            $localUser = new User($this->user->id);
            $this->entityManager->persist($localUser);
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
        $projectCreated = new ProjectCreated($project, $localUser);
        $this->eventBus->handle($projectCreated);
    }
}
