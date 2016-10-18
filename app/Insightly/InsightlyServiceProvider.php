<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use Guzzle\Http\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class InsightlyServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $app
     */
    public function register(Container $app)
    {
        $app['insightly_client'] = function (Container $app) {
            return new InsightlyClient(new Client($app['insightly.host']), $app['insightly.api_key']);
        };
    }
}
