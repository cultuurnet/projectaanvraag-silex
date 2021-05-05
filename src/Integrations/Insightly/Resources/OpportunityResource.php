<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OpportunitySerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use GuzzleHttp\Psr7\Request;

final class OpportunityResource
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

    public function create(Opportunity $opportunity): Id
    {
        $request = new Request(
            'POST',
            'Opportunities/',
            $this->insightlyClient->createHeaders(),
            json_encode((new OpportunitySerializer($this->pipelineStages))->toInsightlyArray($opportunity))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);
        $id = new Id($opportunityAsArray['OPPORTUNITY_ID']);

        $this->updateStage($opportunity->withId($id));

        return $id;
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Opportunities/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Opportunity
    {
        $request = new Request(
            'GET',
            'Opportunities/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);

        return (new OpportunitySerializer($this->pipelineStages))->fromInsightlyArray($opportunityAsArray);
    }

    private function updateStage(Opportunity $opportunity): void
    {
        $stageRequest = new Request(
            'PUT',
            'Opportunities/' . $opportunity->getId()->getValue() . '/Pipeline',
            $this->insightlyClient->createHeaders(),
            json_encode((new OpportunitySerializer($this->pipelineStages))->toInsightlyStageChange($opportunity))
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }
}
