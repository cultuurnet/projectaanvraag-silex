<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class ActivateProjectCommandHandler
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
    protected $cultureFeedLive;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeedLive
     * @param User $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, \CultureFeed $cultureFeedLive, User $user)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->cultureFeedLive = $cultureFeedLive;
        $this->user = $user;
    }

    /**
     * Handle the command
     * @param ActivateProject $activateProject
     */
    public function handle(ActivateProject $activateProject)
    {
        $project = $activateProject->getProject();

        // Create the consumer
        $createConsumer = new \CultureFeed_Consumer();
        $createConsumer->name = $project->getName();
        $createConsumer->description = $project->getDescription();
        $createConsumer->group = [5, $project->getGroupId()];

        // Save consumer in live api.
        /** @var \CultureFeed_Consumer $cultureFeedConsumer */
        $cultureFeedConsumer = $this->cultureFeedLive->createServiceConsumer($createConsumer);

        // Add the user as service consumer admin.
        $this->cultureFeedLive->addServiceConsumerAdmin($cultureFeedConsumer->consumerKey, $this->user->id);

        // Update local db.
        $project->setStatus(ProjectInterface::PROJECT_STATUS_ACTIVE);
        $project->setLiveConsumerKey($cultureFeedConsumer->consumerKey);
        $project->setCoupon($activateProject->getCouponToUse());

        $this->entityManager->persist($project);

        // Mark coupon as used.
        if ($activateProject->getCouponToUse()) {
            /** @var Coupon $coupon */
            $coupon = $this->entityManager->getRepository('ProjectAanvraag:Coupon')->find($activateProject->getCouponToUse());
            $coupon->setUsed(true);
            $this->entityManager->persist($coupon);
        }

        $this->entityManager->flush();

        // Dispatch the event.
        $this->eventBus->handle(new ProjectActivated($project, $activateProject->getCouponToUse()));
    }
}
