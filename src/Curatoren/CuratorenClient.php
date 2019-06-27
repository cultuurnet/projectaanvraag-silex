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

    /**
     * {@inheritdoc}
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * {@inheritdoc}
     */
    public function searchArticles(String $cdbid)
    {
        $options = [
          'query' => ['about' => $cdbid],
        ];

        $result = $this->client->request('GET', 'news_articles', $options);

        return json_decode($result->getBody(true), true);
    }
}
