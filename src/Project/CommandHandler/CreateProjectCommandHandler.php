<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

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
         * 1. Create a test service consumer
         */
        $createConsumer = new \CultureFeed_Consumer();
        $createConsumer->name = $createProject->getName();
        $createConsumer->description = $createProject->getDescription();
        $createConsumer->group = [5, $createProject->getIntegrationType()];

        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = $this->cultureFeedTest->createServiceConsumer($createConsumer);
        $cultureFeedLiveConsumer = null;

        // Create a live service consumer when a coupon is provided
        if (!empty($createProject->getCouponToUse())) {
            /** @var \CultureFeed_Consumer $cultureFeedConsumer */
            $cultureFeedLiveConsumer = $this->cultureFeed->createServiceConsumer($createConsumer);
        }

        /**
         * 2. Save the project to the local database
         */
        $project = new Project();
        $project->setName($cultureFeedConsumer->name);
        $project->setDescription($cultureFeedConsumer->description);
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);
        $project->setTestConsumerKey($cultureFeedConsumer->consumerKey);
        $project->setGroupId($createProject->getIntegrationType());
        $project->setUserId($this->user->id);

        if (!empty($cultureFeedLiveConsumer)) {
            $project->setLiveConsumerKey($cultureFeedLiveConsumer->consumerKey);
        }

        $this->entityManager->persist($project);

        /**
         * 3. Create a local user if needed
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
