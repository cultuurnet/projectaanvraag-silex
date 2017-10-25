<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

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
            $guzzleClient = new GuzzleClient(
                [
                    'timeout' => $app['css_stats.timeout'] ?? 5,
                    'connect_timeout' => $app['css_stats.connect_timeout'] ?? 1,
                ]
            );

            return new CssStatsService($guzzleClient);
        };
    }
}
