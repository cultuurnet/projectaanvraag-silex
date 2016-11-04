<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectBlockedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the command
     * @param ProjectDeleted $projectDeleted
     * @throws \Exception
     */
    public function handle(ProjectBlocked $projectBlocked)
    {
        $this->loadInsightlyProject($projectBlocked);
        $this->insightlyProject->setStatus(Project::STATUS_ABANDONED);
        $this->saveInsightlyProject();
    }
}
