<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Guzzle\Http\Client;

/**
 * Default search client to perform searches on the curatoren api.
 */
class ArticleLinkerClient implements ArticleLinkerClientInterface
{

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * ArticleLinkerClient constructor.
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

    public function linkArticle(String $url, String $cdbid)
    {
        $data = [
          'url' => $url,
          'cdbid' => $cdbid,
        ];

        $this->client->request('POST', 'linkArticle', ["json" => $data]);
    }
}
