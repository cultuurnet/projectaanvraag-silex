<?php

namespace CultuurNet\ProjectAanvraag\CssStats;

use Guzzle\Http\Client;
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
            $guzzleClient = new Client(
                '',
                [
                    'timeout' => $app['css_stats.timeout'] ?? 5,
                    'connect_timeout' => $app['css_stats.connect_timeout'] ?? 1,
                    'curl.options' => [
                        CURLOPT_TIMEOUT => $app['css_stats.timeout'],
                        CURLOPT_CONNECTTIMEOUT => $app['css_stats.connect_timeout'],
                        CURLOPT_RETURNTRANSFER => true,
                    ],
                ]
            );

            return new CssStatsService($guzzleClient);
        };
    }
}
