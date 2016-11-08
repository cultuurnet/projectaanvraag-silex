<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use Guzzle\Http\Client;
use Guzzle\Log\PsrLogAdapter;
use Monolog\Handler\BrowserConsoleHandler;
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
        $app['insightly_client'] = function (Container $app) {

            $logger = new Logger('insightly_api');
            if ($app['debug']) {
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '../../log/insightly.log', 0, Logger::DEBUG));
            }
            else {
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/insightly.log', 0, Logger::DEBUG));
            }

            $logAdapter = new PsrLogAdapter($logger);
            $format = $format = "\n\n# Request:\n{request}\n\n# Response:\n{response}\n\n# Errors: {curl_code} {curl_error}\n\n";
            $logplugin = new \Guzzle\Plugin\Log\LogPlugin(
                $logAdapter,
                $format
            );

            $guzzleClient = new Client($app['insightly.host']);
            $guzzleClient->addSubscriber($logplugin);

            return new InsightlyClient($guzzleClient, $app['insightly.api_key']);
        };
    }
}
