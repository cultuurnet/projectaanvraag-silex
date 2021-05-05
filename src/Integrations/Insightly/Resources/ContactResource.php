<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ContactSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use GuzzleHttp\Psr7\Request;

final class ContactResource
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    public function __construct(InsightlyClient $insightlyClient)
    {
        $this->insightlyClient = $insightlyClient;
    }

    public function create(Contact $contact): Id
    {
        $request = new Request(
            'POST',
            'Contacts/',
            $this->insightlyClient->createHeaders(),
            json_encode((new ContactSerializer())->toInsightlyArray($contact))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $contactAsArray = json_decode($response->getBody()->getContents(), true);

        return new Id($contactAsArray['CONTACT_ID']);
    }

    public function deleteById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Contacts/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getById(Id $id): Contact
    {
        $request = new Request(
            'GET',
            'Contacts/' . $id->getValue(),
            $this->insightlyClient->createHeaders()
        );

        $response = $this->insightlyClient->sendRequest($request);

        $contactAsArray = json_decode($response->getBody()->getContents(), true);

        return (new ContactSerializer())->fromInsightlyArray($contactAsArray);
    }
}
