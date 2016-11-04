<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
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
     * Update a project
     *
     * @param Project $project
     * @param array $options
     *  Array of oData options
     * @return Project
     */
    public function updateProject($project, $options = []);

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
}
