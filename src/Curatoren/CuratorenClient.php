<?php

namespace CultuurNet\ProjectAanvraag\Curatoren;

use GuzzleHttp\ClientInterface;
use Guzzle\Http\Client;

/**
 * Default search client to perform searches on the curatoren api.
 */
class CuratorenClient implements CuratorenClientInterface
{

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * CuratorenClient constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function searchArticles(String $cdbid)
    {
        $options = [
          'query' => ['about' => $cdbid],
        ];

        $result = $this->client->request('GET', 'news_articles', $options);

        return json_decode($result->getBody(true), true);
    }
}
