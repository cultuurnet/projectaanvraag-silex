<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

interface InsightlyClientInterface
{
    /**
     * Gets a list of projects
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getProjects($options = []);

    /**
     * Gets a project
     *
     * @param int $id
     * @return Project
     */
    public function getProject($id);

    /**
     * Gets a list of projects
     *
     * @param Project $project
     * @param array $options
     *  Array of oData options
     * @return Project
     */
    public function updateProject($project, $options = []);

    /**
     * Gets a list of pipelines
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getPipelines($options = []);

    /**
     * Update the pipeline stage for a given project id.
     *
     * @param $projectId
     *   Project id to update.
     * @param $pipelineId
     *   ID of the pipeline to update.
     * @param $newStageId
     *   Id of the new stage.
     * @return Project
     */
    public function updateProjectPipelineStage($projectId, $pipelineId, $newStageId);

    /**
     * @param $organisation
     * @return Organisation
     */
    public function createOrganisation(Organisation $organisation);
}
