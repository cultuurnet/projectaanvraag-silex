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
                return new UitpasClient(new Client($this->getConfig($pimple)));
            };
    
            $pimple['uitpas_api_test'] = function (Container $pimple) {
                $config = $this->getConfig($pimple);
                $config['base_uri'] = $pimple['uitpas_api_test.base_url'];
                $config['headers']['x-client-id'] = $pimple['uitpas_api_test.x_client_id'];
    
                return new UitpasClient(new Client($config));
            };
        }

        private function getConfig(Container $pimple)
        {
            return [
                'base_uri' => $pimple['uitpas_api.base_url'],
                'headers' => [
                    'Content-type' => 'application/json; charset=utf-8',
                    'Accept' => 'application/ld+json',
                    'x-client-id' => $pimple['uitpas_api.x_client_id'],
                ],
                'handler' => $this->getHandlerStack('uitpas_api', $pimple),
            ];
        }

    
}
