<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;

final class ProjectSerializer
{
    private const CUSTOM_FIELD_INTEGRATION_TYPE = 'Product__c';
    private const CUSTOM_FIELD_COUPON = 'Coupon_field__c';

    /**
     * @var PipelineStages
     */
    private $pipelineStages;

    public function __construct(PipelineStages $pipelineStages)
    {
        $this->pipelineStages = $pipelineStages;
    }

    public function toInsightlyArray(Project $project): array
    {
        $opportunityAsArray = [
            'PROJECT_NAME' => $project->getName()->getValue(),
            'STATUS' => $project->getStatus()->getValue(),
            'PROJECT_DETAILS' => $project->getDescription()->getValue(),
            'PIPELINE_ID' => $this->pipelineStages->getOpportunitiesPipelineId(),
            'STAGE_ID' => $this->pipelineStages->getIdFromProjectStage($project->getStage()),
            'CUSTOMFIELDS' => [
                [
                    'FIELD_NAME' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
                    'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_INTEGRATION_TYPE,
                    'FIELD_VALUE' => $project->getIntegrationType()->getValue(),
                ],
                [
                    'FIELD_NAME' => self::CUSTOM_FIELD_COUPON,
                    'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_COUPON,
                    'FIELD_VALUE' => $project->getCoupon()->getValue(),
                ],
            ],
        ];

        if ($project->getId()) {
            $opportunityAsArray['PROJECT_ID'] = $project->getId()->getValue();
        }

        return $opportunityAsArray;
    }

    public function toInsightlyStageChange(ProjectStage $stage): array
    {
        return [
            'PIPELINE_ID' => $this->pipelineStages->getProjectsPipelineId(),
            'PIPELINE_STAGE_CHANGE' => [
                'STAGE_ID' => $this->pipelineStages->getIdFromProjectStage($stage),
            ],
        ];
    }

    public function fromInsightlyArray(array $insightlyArray): Project
    {
        $integrationType = null;
        $coupon = null;
        foreach ($insightlyArray['CUSTOMFIELDS'] as $customField) {
            if ($customField['CUSTOM_FIELD_ID'] === self::CUSTOM_FIELD_INTEGRATION_TYPE) {
                $integrationType = new IntegrationType($customField['FIELD_VALUE']);
            }

            if ($customField['CUSTOM_FIELD_ID'] === self::CUSTOM_FIELD_COUPON) {
                $coupon = new Coupon($customField['FIELD_VALUE']);
            }
        }

        $contactId = (new LinkSerializer())->contactIdFromLinks($insightlyArray['LINKS']);

        return (new Project(
            new Name($insightlyArray['PROJECT_NAME']),
            $this->pipelineStages->getProjectStageFromId($insightlyArray['STAGE_ID']),
            new ProjectStatus($insightlyArray['STATUS']),
            new Description($insightlyArray['PROJECT_DETAILS']),
            $integrationType,
            $coupon,
            $contactId
        ))->withId(
            new Id($insightlyArray['PROJECT_ID'])
        );
    }
}
