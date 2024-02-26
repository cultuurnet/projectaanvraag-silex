<?php

namespace CultuurNet\ProjectAanvraag\UitpasAPI;

use CultuurNet\ProjectAanvraag\APIServiceProviderBase;
use CultuurNet\ProjectAanvraag\Uitpas\UitpasClient;
use GuzzleHttp\Client;
use Pimple\Container;

class UitpasAPIServiceProvider extends APIServiceProviderBase
{
    public function register(Container $pimple)
    {

        $pimple['uitpas_api'] = function (Container $pimple) {

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['uitpas_api.base_url'],
                    'headers' => [
                        'Content-type' => 'application/json; charset=utf-8',
                        'Accept' => 'application/ld+json',
                    ],
                    'handler' => $this->getHandlerStack('uitpas_api', $pimple),
                ]
            );

            return new UitpasClient($guzzleClient);
        };

        $pimple['uitpas_api_test'] = function (Container $pimple) {

            $uitpasClient = clone $pimple['uitpas_api'];

            $config = $uitpasClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['uitpas_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $config['headers'] = $headers;

            $uitpasClient->setClient(new \GuzzleHttp\Client($config));

            return $uitpasClient;
        };
    }
}
