<?php

namespace CultuurNet\ProjectAanvraag\Curatoren;

use GuzzleHttp\ClientInterface;

/**
 * Provides an interface for search clients on the curatoren api
 */
interface CuratorenClientInterface
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
    public function searchArticles(String $cdbid);
}
