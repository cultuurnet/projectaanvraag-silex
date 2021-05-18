<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use CultuurNet\ProjectAanvraag\Project\Event\AbstractProjectEvent;

class ProjectDeletedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the command
     * @param AbstractProjectEvent $projectDeleted
     * @throws \Exception
     */
    public function handle(AbstractProjectEvent $projectDeleted)
    {
        if ($this->useNewInsightlyInstance) {
            return;
        }

        /** @var ProjectDeleted $projectDeleted */
        $this->loadInsightlyProject($projectDeleted);
        $this->insightlyProject->setStatus(Project::STATUS_ABANDONED);
        $this->saveInsightlyProject();
    }
}
