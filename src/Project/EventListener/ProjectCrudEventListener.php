<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Project\Event\AbstractProjectEvent;

/**
 * Abstract event listener for project crud actions to Insightly.
 */
abstract class ProjectCrudEventListener
{
    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * @var \CultuurNet\ProjectAanvraag\Insightly\Item\Project
     */
    protected $insightlyProject;

    /**
     * @var  array
     */
    protected $insightlyConfig;

    /**
     * @var bool
     */
    protected $useNewInsightlyInstance;

    public function __construct(InsightlyClientInterface $insightlyClient, array $insightlyConfig, bool $useNewInsightlyInstance)
    {
        $this->insightlyClient = $insightlyClient;
        $this->insightlyConfig = $insightlyConfig;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
    }

    /**
     * Load the insightly project.
     * @param AbstractProjectEvent $projectEvent
     * @internal param Project $project
     */
    protected function loadInsightlyProject(AbstractProjectEvent $projectEvent)
    {
        $this->insightlyProject = $this->insightlyClient->getProject($projectEvent->getProject()->getInsightlyProjectId());
    }

    protected function saveInsightlyProject()
    {
        $this->insightlyProject = $this->insightlyClient->updateProject($this->insightlyProject);
    }

    /**
     * Save the insightly project.
     */
    protected function createInsightlyProject()
    {
        $this->insightlyProject = $this->insightlyClient->createProject($this->insightlyProject);
    }

    /**
     * Update the pipeline stage for current project.
     * @param int $pipelineId
     * @param int $stageId
     */
    protected function updatePipeline($pipelineId, $stageId)
    {
        $this->insightlyProject = $this->insightlyClient->updateProjectPipeline($this->insightlyProject->getId(), $pipelineId, $stageId);
    }

    /**
     * Update the pipeline stage for current project.
     */
    protected function updatePipelineStage($stageId)
    {
        $this->insightlyProject = $this->insightlyClient->updateProjectPipelineStage($this->insightlyProject->getId(), $stageId);
    }

    /**
     * @return \CultuurNet\ProjectAanvraag\Insightly\Item\Project
     */
    public function getInsightlyProject()
    {
        return $this->insightlyProject;
    }

    /**
     * @param \CultuurNet\ProjectAanvraag\Insightly\Item\Project $insightlyProject
     * @return ProjectCrudEventListener
     */
    public function setInsightlyProject($insightlyProject)
    {
        $this->insightlyProject = $insightlyProject;
        return $this;
    }
}
