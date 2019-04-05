<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * Perform a search on articles.
     *
     * @param String $cdbid
     * @return Array
     */
    public function linkArticle(String $url, String $cdbid);
}
