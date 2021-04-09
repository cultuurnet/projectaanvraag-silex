<?php

namespace CultuurNet\ProjectAanvraag\SearchAPI;

use CultuurNet\ProjectAanvraag\APIServiceProviderBase;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\Serializer;
use GuzzleHttp\Client;
use Pimple\Container;

class SearchAPIServiceProvider extends APIServiceProviderBase
{
    public function register(Container $pimple)
    {

        $pimple['search_api'] = function (Container $pimple) {

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['search_api.base_url'],
                    'headers' => [
                        'X-Api-Key' => $pimple['search_api.api_key'],
                    ],
                    'handler' => $this->getHandlerStack('search_api', $pimple),
                ]
            );

            return new SearchClient($guzzleClient, new Serializer());
        };

        $pimple['search_api_test'] = function (Container $pimple) {

            $searchClient = clone $pimple['search_api'];

            $config = $searchClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['search_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $headers['X-Api-Key'] = $pimple['search_api_test.api_key'];
            $config['headers'] = $headers;

            $searchClient->setClient(new \GuzzleHttp\Client($config));

            return $searchClient;
        };
    }
}
