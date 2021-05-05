<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ProjectSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use GuzzleHttp\Psr7\Request;

final class ProjectResource
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var PipelineStages
     */
    private $pipelineStages;

    public function __construct(InsightlyClient $insightlyClient, PipelineStages $pipelineStages)
    {
        $this->insightlyClient = $insightlyClient;
        $this->pipelineStages = $pipelineStages;
    }

    public function create(Project $project): Id
    {
        $request = new Request(
            'POST',
            'Projects/',
            $this->insightlyClient->createHeaders(),
            json_encode((new ProjectSerializer($this->pipelineStages))->toInsightlyArray($project))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $projectAsArray = json_decode($response->getBody()->getContents(), true);
        $id = new Id($projectAsArray['PROJECT_ID']);

        $this->updateStage($project->withId($id));

        return $id;
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Projects/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Project
    {
        $request = new Request(
            'GET',
            'Projects/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $projectAsArray = json_decode($response->getBody()->getContents(), true);

        return (new ProjectSerializer($this->pipelineStages))->fromInsightlyArray($projectAsArray);
    }

    private function updateStage(Project $project): void
    {
        $stageRequest = new Request(
            'PUT',
            'Projects/' . $project->getId()->getValue() . '/Pipeline',
            $this->insightlyClient->createHeaders(),
            json_encode((new ProjectSerializer($this->pipelineStages))->toInsightlyStageChange($project))
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }
}
