<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline;

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
     * Gets a list of pipelines
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getPipelines($options = []);

    /**
     * Gets a list of pipeline stages
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getPipelineStages($options = []);

    /**
     * Gets a list of contacts
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getContacts($options = []);

    /**
     * Gets a list of product categories
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getProductCategories($options = []);

    /**
     * Gets a list of organisations
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getOrganisations($options = []);

    /**
     * Update a pipeline
     *
     * @param Pipeline $pipeline
     */
    public function updatePipeline(Pipeline $pipeline);

    /**
     * Update an organisation
     *
     * @param EntityInterface $organisation
     */
    public function updateOrganisation(EntityInterface $organisation);

    /**
     * Insert a project
     *
     * @param EntityInterface $project
     */
    public function insertProject(EntityInterface $project);

    /**
     * Insert a contact
     *
     * @param EntityInterface $contact
     */
    public function insertContact(EntityInterface $contact);

    /**
     * Insert an organisation
     *
     * @param EntityInterface $organisation
     */
    public function insertOrganisation(EntityInterface $organisation);
}
