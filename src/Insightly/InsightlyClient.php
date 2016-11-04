<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetContactResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetPipelinesResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectsResult;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\EntityBodyInterface;
use Guzzle\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Guzzle\Http\Message\Response;

class InsightlyClient implements InsightlyClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $guzzleClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    private $responseCache = [];

    /**
     * InsightlyClient constructor.
     * @param ClientInterface $guzzleClient
     * @param $apiKey
     */
    public function __construct(ClientInterface $guzzleClient, $apiKey)
    {
        $this->guzzleClient = $guzzleClient;
        $this->apiKey = $apiKey;
    }

    /**
     * Add filters to the query
     *
     * Accepted options:
     *  - top (int)
     *  - skip (int)
     *  - brief (bool)
     *  - count_total (int)
     *
     * @param array $options
     * @return ParameterBag $query
     */
    private function addQueryFilters(array $options)
    {
        $query = new ParameterBag();

        if (!empty($options['top'])) {
            $query->add(['top' => $options['top']]);
        }

        if (!empty($options['skip'])) {
            $query->add(['skip' => $options['skip']]);
        }

        if (!empty($options['brief'])) {
            $query->add(['brief' => $options['brief'] ? 'true' : 'false']);
        }

        if (!empty($options['count_total'])) {
            $query->add(['count_total' => $options['count_total'] ? 'true' : 'false']);
        }

        return $query;
    }

    /**
     * Returns a cache key for a given request
     *
     * @param $method
     * @param $uri
     * @param ParameterBag $query
     * @return string
     */
    private function getRequestCacheKey($method, $uri, ParameterBag $query)
    {
        return md5($method . $uri . json_encode($query->all()));
    }

    /**
     * Send and handle a request.
     * @param string $method
     * @param string $uri
     * @param ParameterBag $query
     * @param string|resource|array|EntityBodyInterface $body
     * @return Response
     */
    private function request($method, $uri, ParameterBag $query = null, $body = null)
    {
        $query = empty($query) ? new ParameterBag() : $query;
        $cacheKey = $this->getRequestCacheKey($method, $uri, $query);

        if (!isset($this->responseCache[$cacheKey])) {
            $queryParams = !empty($query) ? $query->all() : [];
            $headers = [
                'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
                'Content-Type' => 'application/json',
            ];

            $this->responseCache[$cacheKey] = $this->guzzleClient->createRequest($method, $uri, $headers, $body, ['query' => $queryParams])->send();
        }

        return $this->responseCache[$cacheKey];
    }

    /**
     * {@inheritdoc}
     */
    public function getProjects($options = [])
    {
        $query = $this->addQueryFilters($options);
        return GetProjectsResult::parseToResult($this->request(RequestInterface::GET, 'Projects', $query));
    }

    /**
     * {@inheritdoc}
     */
    public function getProject($id)
    {
        return GetProjectResult::parseToResult($this->request(RequestInterface::GET, 'Projects/' . $id));
    }

    /**
     * {@inheritdoc}
     */
    public function updateProject($project, $options = [])
    {
        $query = $this->addQueryFilters($options);
        return GetProjectResult::parseToResult($this->request(RequestInterface::PUT, 'Projects', $query, json_encode($project->toInsightly())));
    }

    /**
     * {@inheritdoc}
     */
    public function createContact($contact)
    {
        return GetContactResult::parseToResult($this->request(RequestInterface::POST, 'Contacts', null, json_encode($contact->toInsightly())));
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines($options = [])
    {
        $query = $this->addQueryFilters($options);
        return GetPipelinesResult::parseToResult($this->request(RequestInterface::GET, 'Pipelines', $query));
    }
}
