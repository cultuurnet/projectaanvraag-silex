<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;

/**
 * Interface for project services.
 */
interface ProjectServiceInterface
{
    /**
     * Search projects for current user.
     * Optionally filter on name.
     * @param $start
     *   Start index to query
     * @param $max
     *   Maximum results to return
     * @param $name
     *   Name to search on.
     */
    public function searchProjects($start = 0, $max = 20, $name = '');

    /**
     * Load the project by id.
     * @param $id
     * @return Project
     * @throws \Exception
     */
    public function loadProject($id);
}
