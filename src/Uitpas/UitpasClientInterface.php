<?php

namespace CultuurNet\ProjectAanvraag\Uitpas;

use GuzzleHttp\ClientInterface;

/**
 * Provides an interface for search clients on the uitpas api
 */
interface UitpasClientInterface
{
    /**
     * Perform a search on uitpas rewards.
     *
     * @param String $organizerdId
     * @param Int $limit
     * @return Array
     */
    public function searchRewards(String $organizerId, Int $limit);
}
