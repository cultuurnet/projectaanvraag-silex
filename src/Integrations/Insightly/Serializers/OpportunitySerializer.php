<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;

final class OpportunitySerializer
{
    private const CUSTOM_FIELD_INTEGRATION_TYPE = 'Product__c';

    /**
     * @var PipelineStages
     */
    private $pipelineStages;

    public function __construct(PipelineStages $pipelineStages)
    {
        $this->pipelineStages = $pipelineStages;
    }

    public function toInsightlyArray(Opportunity $opportunity): array
    {
        $opportunityAsArray = [
            'OPPORTUNITY_NAME' => $opportunity->getName()->getValue(),
            'OPPORTUNITY_STATE' => $opportunity->getState()->getValue(),
            'OPPORTUNITY_DETAILS' => $opportunity->getDescription()->getValue(),
            'PIPELINE_ID' => $this->pipelineStages->getOpportunitiesPipelineId(),
            'STAGE_ID' => $this->pipelineStages->getIdFromOpportunityStage($opportunity->getStage()),
            'CUSTOMFIELDS' => [
                [
                    'FIELD_NAME' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
                    'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
                    'FIELD_VALUE' => $opportunity->getIntegrationType()->getValue(),
                ],
            ],
        ];

        if ($opportunity->getId()) {
            $opportunityAsArray['OPPORTUNITY_ID'] = $opportunity->getId();
        }

        return $opportunityAsArray;
    }

    public function toInsightlyStageChange(OpportunityStage $stage): array
    {
        return [
            'PIPELINE_ID' => $this->pipelineStages->getOpportunitiesPipelineId(),
            'PIPELINE_STAGE_CHANGE' => [
                'STAGE_ID' => $this->pipelineStages->getIdFromOpportunityStage($stage),
            ],
        ];
    }

    public function fromInsightlyArray(array $insightlyArray): Opportunity
    {
        $integrationType = null;
        foreach ($insightlyArray['CUSTOMFIELDS'] as $customField) {
            if ($customField['CUSTOM_FIELD_ID'] === self::CUSTOM_FIELD_INTEGRATION_TYPE) {
                $integrationType = new IntegrationType($customField['FIELD_VALUE']);
                break;
            }
        }

        return (new Opportunity(
            new Name($insightlyArray['OPPORTUNITY_NAME']),
            new OpportunityState($insightlyArray['OPPORTUNITY_STATE']),
            $this->pipelineStages->getOpportunityStageFromId($insightlyArray['STAGE_ID']),
            new Description($insightlyArray['OPPORTUNITY_DETAILS']),
            $integrationType
        ))->withId(
            new Id($insightlyArray['OPPORTUNITY_ID'])
        );
    }
}
