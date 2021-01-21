<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
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
     * Gets a contact
     *
     * @param int $id
     * @return Contact
     */
    public function getContact($id);

    public function deleteContact(int $id): bool;

    /**
     * Gets a contact by email
     *
     * @param string $email
     * @return Contact
     */
    public function getContactByEmail($email);

    /**
     * Update a project
     *
     * @param Project $project
     * @param array $options
     *  Array of oData options
     * @return Project
     */
    public function updateProject($project, $options = []);

    /**
     * Create a project
     *
     * @param Project $project
     * @param array $options
     *  Array of oData options
     * @return Project
     */
    public function createProject($project, $options = []);

    /**
     * Creates a contact
     *
     * @param Contact $contact
     * @return Contact
     */
    public function createContact($contact);

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
     * @param $newStageId
     *   Id of the new stage.
     * @return Project
     */
    public function updateProjectPipelineStage($projectId, $newStageId);

    /**
     * Update the pipeline for a given project id.
     *
     * @param $projectId
     *   Project id to update.
     * @param $pipelineId
     *   ID of the pipeline to update.
     * @param $newStageId
     *   Id of the new stage.
     * @return Project
     */
    public function updateProjectPipeline($projectId, $pipelineId, $newStageId);


    /**
     * @param $organisation
     * @return Organisation
     */
    public function createOrganisation(Organisation $organisation);

    /**
     * @param int $organisationId
     * @return Organisation
     */
    public function getOrganisation($organisationId);

    /**
     * Update an organisation
     *
     * @param $organisation
     * @return Organisation
     */
    public function updateOrganisation(Organisation $organisation);
}
