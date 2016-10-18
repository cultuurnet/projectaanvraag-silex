<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;

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
     * Gets a list of stages
     *
     * @param array $options
     *  Array of oData options
     * @return EntityList
     */
    public function getStages($options = []);

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
}
