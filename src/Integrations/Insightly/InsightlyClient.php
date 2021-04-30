<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\AuthenticationFailed;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\BadRequest;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\DeleteFailed;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordLimitReached;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ContactSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OpportunitySerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class InsightlyClient
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(ClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function createContact(Contact $contact): Id
    {
        $request = new Request(
            'POST',
            'Contacts/',
            $this->createHeaders(),
            json_encode((new ContactSerializer())->toInsightlyArray($contact))
        );

        $response = $this->sendRequest($request);

        $contactAsArray = json_decode($response->getBody()->getContents(), true);

        return new Id($contactAsArray['CONTACT_ID']);
    }

    public function deleteContactById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Contacts/' . $id->getValue(),
            $this->createHeaders()
        );

        $this->sendRequest($request);
    }

    public function getContactById(Id $id): Contact
    {
        $request = new Request(
            'GET',
            'Contacts/' . $id->getValue(),
            $this->createHeaders()
        );

        $response = $this->sendRequest($request);

        $contactAsArray = json_decode($response->getBody()->getContents(), true);

        return (new ContactSerializer())->fromInsightlyArray($contactAsArray);
    }

    public function createOpportunity(Opportunity $opportunity): Id
    {
        $request = new Request(
            'POST',
            'Opportunities/',
            $this->createHeaders(),
            json_encode((new OpportunitySerializer())->toInsightlyArray($opportunity))
        );

        $response = $this->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);

        return new Id($opportunityAsArray['OPPORTUNITY_ID']);
    }

    public function deleteOpportunityById(Id $id): void
    {
        $request = new Request(
            'DELETE',
            'Opportunities/' . $id->getValue(),
            $this->createHeaders()
        );

        $this->sendRequest($request);
    }

    public function getOpportunityById(Id $id): Opportunity
    {
        $request = new Request(
            'GET',
            'Opportunities/' . $id->getValue(),
            $this->createHeaders()
        );

        $response = $this->sendRequest($request);

        $opportunityAsArray = json_decode($response->getBody()->getContents(), true);

        return (new OpportunitySerializer())->fromInsightlyArray($opportunityAsArray);
    }

    private function createHeaders(): array
    {
        return [
            'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
            'Content-Type' => 'application/json',
        ];
    }

    private function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->httpClient->send($request);
        $this->validateResponse($response);

        return $response;
    }

    private function validateResponse(ResponseInterface $response): void
    {
        switch ($response->getStatusCode()) {
            case 400:
                throw new BadRequest($response->getReasonPhrase());
            case 401:
                throw new AuthenticationFailed($response->getReasonPhrase());
            case 402:
                throw new RecordLimitReached($response->getReasonPhrase());
            case 404:
                throw new RecordNotFound($response->getReasonPhrase());
            case 417:
                throw new DeleteFailed($response->getReasonPhrase());
        }
    }
}
