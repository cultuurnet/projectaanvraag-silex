<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectDeletedEventListener extends ProjectCrudEventListener
{

    /**
     * Handle the command
     * @param ProjectDeleted $projectDeleted
     * @throws \Exception
     */
    public function handle(ProjectDeleted $projectDeleted)
    {
        $this->loadInsightlyProject($projectDeleted);
        $this->insightlyProject->setStatus(Project::STATUS_ABANDONED);
        $this->saveInsightlyProject();
    }
}
