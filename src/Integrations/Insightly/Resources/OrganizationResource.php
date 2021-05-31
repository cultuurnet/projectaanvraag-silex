<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\CustomFieldSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OrganizationSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\TaxNumber;
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
            'Organizations/',
            [],
            json_encode($this->organizationSerializer->toInsightlyArray($organization))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = json_decode($response->getBody()->getContents(), true);
        return new Id($organizationAsArray['ORGANISATION_ID']);
    }

    public function update(Organization $organization): void
    {
        if ($organization->getId() === null) {
            throw new \InvalidArgumentException('Id of the organization is required');
        }

        $request = new Request(
            'PUT',
            'Organizations/',
            [],
            json_encode($this->organizationSerializer->toInsightlyArray($organization))
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Organizations/' . $id->getValue()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Organization
    {
        $request = new Request(
            'GET',
            'Organizations/' . $id->getValue()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = json_decode($response->getBody()->getContents(), true);

        return ($this->organizationSerializer->fromInsightlyArray($organizationAsArray));
    }

    public function getByTaxNumber(TaxNumber $taxNumber): Organization
    {
        $request = new Request(
            'GET',
            'Organizations/Search/?field_name=' . CustomFieldSerializer::CUSTOM_FIELD_TAX_NUMBER .
            '&field_value=' . $taxNumber->getValue() . '&top=1'
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = json_decode($response->getBody()->getContents(), true);

        if (count($organizationAsArray) < 1) {
            throw new RecordNotFound('No organizations found with tax number ' . $taxNumber->getValue());
        }

        return ($this->organizationSerializer->fromInsightlyArray($organizationAsArray[0]));
    }

    public function getByEmail(Email $email): Organization
    {
        $request = new Request(
            'GET',
            'Organizations/Search/?field_name=' . CustomFieldSerializer::CUSTOM_FIELD_EMAIL .
            '&field_value=' . $email->getValue() . '&top=1'
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = json_decode($response->getBody()->getContents(), true);

        if (count($organizationAsArray) < 1) {
            throw new RecordNotFound('No organizations found with email ' . $email->getValue());
        }

        return ($this->organizationSerializer->fromInsightlyArray($organizationAsArray[0]));
    }
    private function linkContact(Id $organizationId, Id $contactId): void
    {
        $request = new Request(
            'POST',
            'Organizations/' . $organizationId->getValue() . '/Links',
            [],
            json_encode((new LinkSerializer())->contactIdToLink($contactId))
        );

        $this->insightlyClient->sendRequest($request);
    }
}
