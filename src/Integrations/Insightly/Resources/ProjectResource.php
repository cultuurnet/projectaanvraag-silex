<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ProjectSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use GuzzleHttp\Psr7\Request;

final class ProjectResource
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var ProjectSerializer
     */
    private $projectSerializer;

    public function __construct(InsightlyClient $insightlyClient, PipelineStages $pipelineStages)
    {
        $this->insightlyClient = $insightlyClient;
        $this->projectSerializer = new ProjectSerializer($pipelineStages);
    }

    public function create(Project $project): Id
    {
        $request = new Request(
            'POST',
            'Projects/',
            [],
            json_encode($this->projectSerializer->toInsightlyArray($project))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $projectAsArray = json_decode($response->getBody()->getContents(), true);
        $id = new Id($projectAsArray['PROJECT_ID']);

        $this->updateStage($id, $project->getStage());

        return $id;
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Projects/' . $id->getValue()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Project
    {
        $request = new Request(
            'GET',
            'Projects/' . $id->getValue()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $projectAsArray = json_decode($response->getBody()->getContents(), true);

        return $this->projectSerializer->fromInsightlyArray($projectAsArray);
    }

    private function updateStage(Id $id, ProjectStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            'Projects/' . $id->getValue() . '/Pipeline',
            [],
            json_encode($this->projectSerializer->toInsightlyStageChange($stage))
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }
}
