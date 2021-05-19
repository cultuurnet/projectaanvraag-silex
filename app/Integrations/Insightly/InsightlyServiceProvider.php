<?php

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use GuzzleHttp\Client;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class InsightlyServiceProvider implements ServiceProviderInterface
{
    /**
     * @param \Pimple\Container $app
     */
    public function register(Container $app)
    {
        $app['insightly_logger'] = function (Container $app) {
            $logger = new Logger('insightly_api');
            $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../../log/integrations/insightly.log', 0, Logger::DEBUG));
            return $logger;
        };

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

        $app['group_id_converter'] = function (Container $app) {
            return new GroupIdConverter($app['integration_types.storage']);
        };
    }
}
