<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

use Goutte\Client;
use GuzzleHttp\Client as GuzzleClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CssStatsServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $app
     */
    public function register(Container $app)
    {
        $app['css_stats'] = function (Container $app) {
            $goutteClient = new Client();
            $guzzleClient = new GuzzleClient(
                [
                    'timeout' => $app['css_stats.timeout'] ?? 5,
                    'connect_timeout' => $app['css_stats.connect_timeout'] ?? 1,
                ]
            );

            $goutteClient->setClient($guzzleClient);

            return new CssStatsService($guzzleClient);
        };
    }
}
