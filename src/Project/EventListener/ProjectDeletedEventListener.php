<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;

class ProjectDeletedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the command
     * @param ProjectEvent $projectDeleted
     * @throws \Exception
     */
    public function handle(ProjectEvent $projectDeleted)
    {
        parent::handle($projectDeleted);

        /** @var ProjectDeleted $projectDeleted */
        $this->loadInsightlyProject($projectDeleted);
        $this->insightlyProject->setStatus(Project::STATUS_ABANDONED);
        $this->saveInsightlyProject();
    }
}
