<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;

class ProjectBlockedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the command
     * @param ProjectEvent $projectBlocked
     * @throws \Exception
     */
    public function handle(ProjectEvent $projectBlocked)
    {
        /** @var ProjectBlocked $projectBlocked */
        $this->loadInsightlyProject($projectBlocked);
        $this->insightlyProject->setStatus(Project::STATUS_CANCELLED);
        $this->saveInsightlyProject();
    }
}
