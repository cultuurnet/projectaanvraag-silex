<?php

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use GuzzleHttp\Client;
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
            return new InsightlyClient(
                new Client(
                    [
                        'base_uri' => $app['integrations.insightly.host'],
                        'http_errors' => false,
                    ]
                ),
                $app['integrations.insightly.api_key'],
                new PipelineStages($app['integrations.insightly.pipelines'])
            );
        };
    }
}
