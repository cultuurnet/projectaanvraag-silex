<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectDeletedEventListener
{
    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * ProjectDeletedEventListener constructor.
     * @param InsightlyClientInterface $insightlyClient
     */
    public function __construct(InsightlyClientInterface $insightlyClient)
    {
        $this->insightlyClient = $insightlyClient;
    }

    /**
     * Handle the command
     * @param ProjectDeleted $projectDeleted
     * @throws \Exception
     */
    public function handle($projectDeleted)
    {
        /** @var ProjectInterface $project */
        $project = $projectDeleted->getProject();

        /**
         * Load the project from Insightly
         * @var Project $insightlyProject
         */
        $insightlyProject = $this->insightlyClient->getProject($project->getInsightlyProjectId());
        $insightlyProject->setStatus(Project::STATUS_ABANDONED);


        // Update the Insightly project
        $this->insightlyClient->updateProject($insightlyProject);
    }
}
