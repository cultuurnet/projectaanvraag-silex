<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides an interface for search clients on the curatoren api
 */
interface ArticleLinkerClientInterface
{

    /**
     * Set the guzzle client.
     *
     * @param ClientInterface $client
     */
    public function setClient(ClientInterface $client);

    /**
     * Return the current client.
     *
     * @return ClientInterface $client
     */
    public function getClient();

    public function linkArticle(String $url, String $cdbid): void;
}
