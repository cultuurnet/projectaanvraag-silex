<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline;
use CultuurNet\ProjectAanvraag\Insightly\Result\EntityListResultHandler;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetPipelinesResult;
use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectsResult;
use Guzzle\Http\ClientInterface;
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
     * @param ParameterBag $query
     * @param array $options
     * @return ParameterBag $query
     */
    private function addQueryFilters(ParameterBag $query, array $options)
    {
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
     * @param array $body
     * @return Response
     */
    private function request($method, $uri, ParameterBag $query = null, $body = [])
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
        $query = $this->addQueryFilters(new ParameterBag(), $options);
        return GetProjectsResult::parseToResult($this->request(RequestInterface::GET, 'Projects', $query));
    }

    public function getPipelines($options = [])
    {
        $query = $this->addQueryFilters(new ParameterBag(), $options);
        return GetPipelinesResult::parseToResult($this->request(RequestInterface::GET, 'Pipelines', $query));
    }

    public function getPipelineStages($options = [])
    {
        // TODO: Implement getPipelineStages() method.
    }

    public function getContacts($options = [])
    {
        // TODO: Implement getContacts() method.
    }

    public function getProductCategories($options = [])
    {
        // TODO: Implement getProductCategories() method.
    }

    public function getOrganisations($options = [])
    {
        // TODO: Implement getOrganisations() method.
    }

    public function updatePipeline(Pipeline $pipeline)
    {
        // TODO: Implement updatePipeline() method.
    }

    public function updateOrganisation(EntityInterface $organisation)
    {
        // TODO: Implement updateOrganisation() method.
    }

    public function insertProject(EntityInterface $project)
    {
        // TODO: Implement insertProject() method.
    }

    public function insertContact(EntityInterface $contact)
    {
        // TODO: Implement insertContact() method.
    }

    public function insertOrganisation(EntityInterface $organisation)
    {
        // TODO: Implement insertOrganisation() method.
    }
}
