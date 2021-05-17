<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ContactSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

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
            [],
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
            'Contacts/' . $id->getValue()
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function getByEmail(Email $email): Contact
    {
        $request = new Request(
            'GET',
            'Contacts/Search/?field_name=EMAIL_ADDRESS&field_value=' . $email->getValue() . '&top=1'
        );

        $response = $this->insightlyClient->sendRequest($request);

        $contactsAsArray = json_decode($response->getBody()->getContents(), true);

        if (count($contactsAsArray) < 1) {
            throw new RecordNotFound('No contacts found with email ' . $email->getValue());
        }

        return (new ContactSerializer())->fromInsightlyArray($contactsAsArray[0]);
    }
}
