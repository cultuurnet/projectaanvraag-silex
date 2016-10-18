<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Result\GetProjectsResult;
use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\BadResponseException;
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
   * Add OData query filters to the query
   *
   * Accepted options:
   *  - top
   *  - skip
   *  - orderby
   *  - an array of filters
   *
   * @param ParameterBag $query
   * @param array $options
   * @return ParameterBag $query
   * @link http://www.odata.org/documentation/odata-version-2-0/uri-conventions/
   */
  private function buildODataQuery(ParameterBag $query, array $options)
  {
    if(!empty($options['top'])){
      $query->add(['top' => $options['top']]);
    }

    if(!empty($options['skip'])){
      $query->add(['skip'=> $options['skip']]);
    }

    if(!empty($options['orderby'])){
      $query->add(['orderby' => $options['orderby']]);
    }

    if(!empty($options['brief'])){
      $query->add(['brief' => $options['brief'] ? 'true' : 'false']);
    }

    if(!empty($options['count_total'])){
      $query->add(['count_total' => $options['count_total'] ? 'true' : 'false']);
    }

    if(!empty($options['filters']) && is_array($options['filters'])){
      foreach($options['filters'] as $filter){
        $filterValue = str_replace(['=', '>', '<'], [' eq ', ' gt ', ' lt '], $filter);
        $query->add(['filter' => $filterValue]);
      }
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
  private function getRequestCacheKey($method, $uri, ParameterBag $query) {
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
    $cache_key = $this->getRequestCacheKey($method, $uri, $query);

    if (!isset($this->responseCache[$cache_key])) {
      try {
        $queryParams = !empty($query) ? $query->all() : [];
        $headers = [
          'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':'),
          'Content-Type' => 'application/json',
        ];

        $response = $this->guzzleClient->createRequest($method, $uri, $headers, $body, ['query' => $queryParams])->send();
      } catch (BadResponseException $e) {
        $response = $e->getResponse();
      }

      $this->responseCache[$cache_key] = $response;
    }

    return $this->responseCache[$cache_key];
  }

  /**
   * {@inheritdoc}
   */
  public function getProjects($options = [])
  {
    $query = $this->buildODataQuery(new ParameterBag(), $options);
    return GetProjectsResult::parseToResult($this->request(RequestInterface::GET, 'Projects', $query));
  }

  public function getPipelines($options = [])
  {
    $query = $this->buildODataQuery(new ParameterBag(), $options);
    return $this->request(RequestInterface::GET, 'Pipelines', $query);
  }

  public function getStages($options = [])
  {
    // TODO: Implement getStages() method.
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
}