<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\AbstractProjectEvent;

class ProjectBlockedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the command
     * @param AbstractProjectEvent $projectBlocked
     * @throws \Exception
     */
    public function handle(AbstractProjectEvent $projectBlocked)
    {
        if ($this->newInsightlyInstance) {
            return;
        }

        /** @var ProjectBlocked $projectBlocked */
        $this->loadInsightlyProject($projectBlocked);
        $this->insightlyProject->setStatus(Project::STATUS_CANCELLED);
        $this->saveInsightlyProject();
    }
}
