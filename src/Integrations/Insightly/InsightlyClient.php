<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\AuthenticationFailed;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\BadRequest;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\DeleteFailed;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordLimitReached;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources\ContactResource;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources\OpportunityResource;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Resources\ProjectResource;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ContactSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OpportunitySerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\ProjectSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
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

    /**
     * @var PipelineStages
     */
    private $pipelineStages;

    public function __construct(ClientInterface $httpClient, string $apiKey, PipelineStages $pipelineStages)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
        $this->pipelineStages = $pipelineStages;
    }

    public function contactResource(): ContactResource
    {
        return new ContactResource($this);
    }

    public function opportunityResource(): OpportunityResource
    {
        return new OpportunityResource($this, $this->pipelineStages);
    }

    public function projectResource(): ProjectResource
    {
        return new ProjectResource($this, $this->pipelineStages);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $requestWithHeaders = $request
            ->withAddedHeader(
                'Authorization',
                'Basic ' . base64_encode($this->apiKey . ':')
            )
            ->withAddedHeader(
                'Content-Type',
                'application/json'
            );

        $response = $this->httpClient->send($requestWithHeaders);
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
