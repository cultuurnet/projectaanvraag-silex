<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class RequestActivationCommandHandler
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
     * @var UserInterface
     */
    protected $user;

    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * @var array
     */
    protected $insightlyConfig;

    /**
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeedLive
     * @param User $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, User $user, InsightlyClientInterface $insightlyClient, $insightlyConfig)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->insightlyClient = $insightlyClient;
        $this->insightlyConfig = $insightlyConfig;
    }

    /**
     * Handle the command
     * @param RequestActivation $requestActivation
     * @throws \Throwable
     */
    public function handle(RequestActivation $requestActivation)
    {
        $project = $requestActivation->getProject();
        $insightlyProject = $this->insightlyClient->getProject($project->getInsightlyProjectId());

        // Update the pipeline stage.
        $this->insightlyClient->updateProjectPipelineStage(
            $project->getInsightlyProjectId(),
            $this->insightlyConfig['stages']['pipeline'],
            $this->insightlyConfig['stages']['aanvraag']
        );

        // Create an organisation. (We can't search on VAT, so always create a new)


        // Update the project state in local db.
        $project->setStatus(ProjectInterface::PROJECT_STATUS_WAITING_FOR_PAYMENT);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        //

    }
}
