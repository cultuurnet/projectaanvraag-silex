<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectBlockedEventListener
{
    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * ProjectBlockedEventListener constructor.
     * @param InsightlyClientInterface $insightlyClient
     */
    public function __construct(InsightlyClientInterface $insightlyClient)
    {
        $this->insightlyClient = $insightlyClient;
    }

    /**
     * Handle the command
     * @param ProjectBlocked $projectBlocked
     * @throws \Exception
     */
    public function handle($projectBlocked)
    {
        /** @var ProjectInterface $project */
        $project = $projectBlocked->getProject();

        /**
         * Load the project from Insightly
         * @var Project $insightlyProject
         */
        $insightlyProject = $this->insightlyClient->getProject($project->getInsightlyProjectId());
        $insightlyProject->setStatus(Project::STATUS_CANCELLED);


        // Update the Insightly project
        $this->insightlyClient->updateProject($insightlyProject);
    }
}
