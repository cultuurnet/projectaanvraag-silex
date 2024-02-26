<?php

namespace CultuurNet\ProjectAanvraag\Uitpas;

use GuzzleHttp\ClientInterface;

/**
 * Default client to perform searches on the uitpas api.
 */
class UitpasClient implements UitpasClientInterface
{

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * UitpasClient constructor.
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

    public function searchRewards(String $organizerId)
    {
        $options = [
          'query' => ['organizerId' => $organizerId],
        ];

        $result = $this->client->request('GET', 'rewards', $options);

        return json_decode($result->getBody(true), true);
    }
}
