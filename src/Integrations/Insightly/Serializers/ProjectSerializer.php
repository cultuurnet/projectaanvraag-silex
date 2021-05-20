<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;

final class ProjectSerializer
{
    /**
     * @var PipelineStages
     */
    private $pipelineStages;

    /**
     * @var CustomFieldSerializer
     */
    private $customFieldSerializer;

    public function __construct(PipelineStages $pipelineStages)
    {
        $this->pipelineStages = $pipelineStages;
        $this->customFieldSerializer = new CustomFieldSerializer();
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
                $this->customFieldSerializer->integrationTypeToCustomField($project->getIntegrationType()),
                $this->customFieldSerializer->couponToCustomField($project->getCoupon()),
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
        return (new Project(
            new Name($insightlyArray['PROJECT_NAME']),
            $this->pipelineStages->getProjectStageFromId($insightlyArray['STAGE_ID']),
            new ProjectStatus($insightlyArray['STATUS']),
            new Description($insightlyArray['PROJECT_DETAILS']),
            $this->customFieldSerializer->getIntegrationType($insightlyArray['CUSTOMFIELDS']),
            $this->customFieldSerializer->getCoupon($insightlyArray['CUSTOMFIELDS'])
        ))->withId(
            new Id($insightlyArray['PROJECT_ID'])
        );
    }
}
