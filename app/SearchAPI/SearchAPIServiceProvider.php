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

            $headers = [];
            if ($pimple['search_api.use_client_ids']) {
                $headers['X-Client-Id'] = $pimple['search_api.client_id'];
            } else {
                $headers['X-Api-Key'] = $pimple['search_api.api_key'];
            }
            $headers['X-Client-Properties'] = 'cluster|widgets, cluster|snowplow';

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['search_api.base_url'],
                    'headers' => $headers,
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
            if ($pimple['search_api.use_client_ids']) {
                $headers['X-Client-Id'] = $pimple['search_api_test.client_id'];
            } else {
                $headers['X-Api-Key'] = $pimple['search_api_test.api_key'];
            }
            $config['headers'] = $headers;

            $searchClient->setClient(new \GuzzleHttp\Client($config));

            return $searchClient;
        };
    }
}
