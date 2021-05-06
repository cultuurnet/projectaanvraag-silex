<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OrganizationSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use GuzzleHttp\Psr7\Request;

final class OrganizationResource
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var OrganizationSerializer
     */
    private $organizationSerializer;

    public function __construct(InsightlyClient $insightlyClient)
    {
        $this->insightlyClient = $insightlyClient;
        $this->organizationSerializer = new OrganizationSerializer();
    }

    public function create(Organization $organization): Id
    {
        $request = new Request(
            'POST',
            'Opportunities/',
            [],
            json_encode($this->organizationSerializer->toInsightlyArray($organization))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = json_decode($response->getBody()->getContents(), true);
        return new Id($organizationAsArray['ORGANISATION_ID']);
    }
}
