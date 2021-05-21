<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\Project\Event\AbstractProjectEvent;

/**
 * Event listener to handle project activation in insightly.
 */
class ProjectActivatedEventListener extends ProjectCrudEventListener
{
    /**
     * Handle the event
     * @param AbstractProjectEvent $projectActivated
     */
    public function handle(AbstractProjectEvent $projectActivated)
    {
        if ($this->useNewInsightlyInstance) {
            return;
        }

        /** @var ProjectActivated $projectActivated */
        $this->loadInsightlyProject($projectActivated);
        $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['live_key'], $projectActivated->getProject()->getLiveConsumerKey());

        if (!empty($this->insightlyConfig['custom_fields']['last_activation_date'])) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['last_activation_date'], date('Y-m-d H:i'));
        }

        if ($projectActivated->getUsedCoupon() && !empty($this->insightlyConfig['custom_fields']['used_coupon'])) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['used_coupon'], $projectActivated->getUsedCoupon());
        }

        $this->insightlyProject->setStatus(Project::STATUS_COMPLETED);

        $this->saveInsightlyProject();

        if ($projectActivated->getUsedCoupon()) {
            $this->updatePipelineStage($this->insightlyConfig['stages']['live_met_coupon']);
        } else {
            $this->updatePipelineStage($this->insightlyConfig['stages']['live_met_abonnement']);
        }
    }
}
