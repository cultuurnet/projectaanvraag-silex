<?php

namespace CultuurNet\ProjectAanvraag\Project;

/**
 * Interface for project services.
 */
interface ProjectServiceInterface
{

    /**
     * Load the projects for current user.
     */
    public function loadProjects();

}