<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use GuzzleHttp\ClientInterface;

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
     * Link an article.
     *
     * @param String $url
     * @param String $cdbid
     * @return array
     */
    public function linkArticle(string $url, string $cdbid);
}
