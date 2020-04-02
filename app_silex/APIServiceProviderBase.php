<?php
/**
 * Created by PhpStorm.
 * User: nils
 * Date: 2019-06-26
 * Time: 14:38
 */

namespace CultuurNet\ProjectAanvraag;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

abstract class APIServiceProviderBase implements ServiceProviderInterface
{

    /**
     * Get the handlerstack for the API client.
     * @param $settingsPrefix
     *   The config prefix.
     * @param Container $pimple
     *   The pimple container.
     */
    protected function getHandlerStack($configPrefix, Container $pimple)
    {

        $handlerStack = HandlerStack::create();

        if ($pimple[$configPrefix . '.cache.enabled']) {
            $handlerStack->push(
                new CacheMiddleware(
                    new PrivateCacheStrategy(
                        new DoctrineCacheStorage(
                            $pimple['cache_doctrine_' . $pimple[$configPrefix . '.cache.backend']]
                        )
                    )
                ),
                'cache'
            );
        }

        if ($pimple['debug']) {
            $logger = new Logger($configPrefix);
            $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
            $logName = str_replace('_', '-', $configPrefix);
            $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../log/' . $logName . '/' . $logName . '.log', 0, Logger::DEBUG));

            $handlerStack->push(
                Middleware::log(
                    $logger,
                    new MessageFormatter(MessageFormatter::SHORT)
                )
            );
        }

        return $handlerStack;
    }
}
