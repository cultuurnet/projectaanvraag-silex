<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\LinkSerializer;
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
     * @var OpportunitySerializer
     */
    private $opportunitySerializer;

    public function __construct(InsightlyClient $insightlyClient, PipelineStages $pipelineStages)
    {
        $this->insightlyClient = $insightlyClient;
        $this->opportunitySerializer = new OpportunitySerializer($pipelineStages);
    }

    public function createWithContact(Opportunity $opportunity, Id $contactId): Id
    {
        $request = new Request(
            'POST',
            'Opportunities/',
            [],
            json_encode($this->opportunitySerializer->toInsightlyArray($opportunity))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);
        $id = new Id($opportunityAsArray['OPPORTUNITY_ID']);

        $this->updateStage($id, $opportunity->getStage());

        $this->linkContact($id, $contactId);

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

        return ($this->opportunitySerializer->fromInsightlyArray($opportunityAsArray));
    }

    public function updateStage(Id $id, OpportunityStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            'Opportunities/' . $id->getValue() . '/Pipeline',
            [],
            json_encode($this->opportunitySerializer->toInsightlyStageChange($stage))
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }

    public function getLinkedContactId(Id $id): Id
    {
        $request = new Request(
            'GET',
            'Opportunities/' . $id->getValue()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);

        return (new LinkSerializer())->contactIdFromLinks($opportunityAsArray['LINKS']);
    }

    private function linkContact(Id $opportunityId, Id $contactId): void
    {
        $request = new Request(
            'POST',
            'Opportunities/' . $opportunityId->getValue() . '/Links',
            [],
            json_encode((new LinkSerializer())->contactIdToLink($contactId))
        );

        $this->insightlyClient->sendRequest($request);
    }
}
