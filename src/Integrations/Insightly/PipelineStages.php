<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;

final class PipelineStages
{
    /**
     * @var array
     */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getOpportunitiesPipelineId(): int
    {
        return $this->mapping['opportunities']['id'];
    }

    public function getIdFromOpportunityStage(OpportunityStage $opportunityStage): int
    {
        return $this->mapping['opportunities']['stages'][$opportunityStage->getValue()];
    }

    public function getOpportunityStageFromId(int $id): OpportunityStage
    {
        $key = array_search($id, $this->mapping['opportunities']['stages'], false);
        return new OpportunityStage($key);
    }

    public function getProjectsPipelineId(): int
    {
        return $this->mapping['projects']['id'];
    }
}
