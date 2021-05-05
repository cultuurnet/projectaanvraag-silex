<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OpportunitySerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
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
            [],
            json_encode((new OpportunitySerializer($this->pipelineStages))->toInsightlyArray($opportunity))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);
        $id = new Id($opportunityAsArray['OPPORTUNITY_ID']);

        $this->updateStage($id, $opportunity->getStage());

        return $id;
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Opportunities/' . $id->getValue()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Opportunity
    {
        $request = new Request(
            'GET',
            'Opportunities/' . $id->getValue()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);

        return (new OpportunitySerializer($this->pipelineStages))->fromInsightlyArray($opportunityAsArray);
    }

    private function updateStage(Id $id, OpportunityStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            'Opportunities/' . $id->getValue() . '/Pipeline',
            [],
            json_encode((new OpportunitySerializer($this->pipelineStages))->toInsightlyStageChange($stage))
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }
}
