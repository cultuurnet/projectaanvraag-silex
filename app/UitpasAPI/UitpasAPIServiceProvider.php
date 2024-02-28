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
            return new UitpasClient(new Client($this->getConfig($pimple, false)));
        };

        $pimple['uitpas_api_test'] = function (Container $pimple) {
            return new UitpasClient(new Client($this->getConfig($pimple, true)));
        };
    }

    private function getConfig(Container $pimple, bool $test): array
    {
        return [
            'base_uri' => $test ? $pimple['uitpas_api_test.base_url'] : $pimple['uitpas_api.base_url'],
            'headers' => [
                'Content-type' => 'application/json; charset=utf-8',
                'Accept' => 'application/ld+json',
                'x-client-id' => $test ? $pimple['uitpas_api_test.x_client_id'] : $pimple['uitpas_api.x_client_id'],
            ],
            'handler' => $this->getHandlerStack('uitpas_api', $pimple),
        ];
    }
}
