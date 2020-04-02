<?php

namespace CultuurNet\ProjectAanvraag\CuratorenAPI;

use CultuurNet\ProjectAanvraag\APIServiceProviderBase;
use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use GuzzleHttp\Client;
use Pimple\Container;

class CuratorenAPIServiceProvider extends APIServiceProviderBase
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {

        $pimple['curatoren_api'] = function (Container $pimple) {

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['curatoren_api.base_url'],
                    'headers' => [
                        'Content-type' => 'application/json; charset=utf-8',
                        'Accept' => 'application/ld+json',
                    ],
                    'handler' => $this->getHandlerStack('curatoren_api', $pimple),
                ]
            );

            return new CuratorenClient($guzzleClient);
        };

        $pimple['curatoren_api_test'] = function (Container $pimple) {

            $curatorenClient = clone $pimple['curatoren_api'];

            $config = $curatorenClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['curatoren_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $config['headers'] = $headers;

            $curatorenClient->setClient(new \GuzzleHttp\Client($config));

            return $curatorenClient;
        };
    }
}
