<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;

/**
 * Event listener to handle project activation in insightly.
 */
class ProjectActivatedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the event
     * @param ProjectEvent $projectActivated
     */
    public function handle(ProjectEvent $projectActivated)
    {
        /** @var ProjectActivated $projectActivated */
        $this->loadInsightlyProject($projectActivated);
        $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['live_key'], $projectActivated->getProject()->getLiveConsumerKey());

        if ($projectActivated->getUsedCoupon() && !empty($this->insightlyConfig['custom_fields']['used_coupon'])) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['used_coupon'], $projectActivated->getUsedCoupon());
        }

        $this->saveInsightlyProject();

        $this->insightlyProject->setStatus(Project::STATUS_COMPLETED);

        if ($projectActivated->getUsedCoupon()) {
            $this->updatePipelineStage($this->insightlyConfig['stages']['live_met_coupon']);
        } else {
            $this->updatePipelineStage($this->insightlyConfig['stages']['live_met_abonnement']);
        }
    }
}
