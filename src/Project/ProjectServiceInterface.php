<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;

/**
 * Interface for project services.
 */
interface ProjectServiceInterface
{
    /**
     * Load the projects for current user.
     * @param int $start
     * @param int $max
     * @return array
     */
    public function loadProjects($start, $max);

    /**
     * Load the project by id.
     * @param $id
     * @return Project
     * @throws \Exception
     */
    public function loadProject($id);
}
