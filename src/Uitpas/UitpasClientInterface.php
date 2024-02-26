<?php

namespace CultuurNet\ProjectAanvraag\Uitpas;

use GuzzleHttp\ClientInterface;

/**
 * Provides an interface for search clients on the uitpas api
 */
interface UitpasClientInterface
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
     * Perform a search on uitpas rewards.
     *
     * @param String $organizerdId
     * @return Array
     */
    public function searchRewards(String $organizerId);
}
