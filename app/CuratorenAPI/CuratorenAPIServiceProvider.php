<?php

namespace CultuurNet\ProjectAanvraag\CuratorenAPI;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use Guzzle\Cache\DoctrineCacheAdapter;
use GuzzleHttp\Client;
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

class CuratorenAPIServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {

        $pimple['curatoren_api'] = function (Container $pimple) {

            $handlerStack = HandlerStack::create();

            if ($pimple['curatoren_api.cache.enabled']) {
                $handlerStack->push(
                    new CacheMiddleware(
                        new PrivateCacheStrategy(
                            new DoctrineCacheStorage(
                                $pimple['cache_doctrine_' . $pimple['curatoren_api.cache.backend']]
                            )
                        )
                    ),
                    'cache'
                );
            }

            if ($pimple['debug']) {
                $logger = new Logger('curatoren_api');
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/curatoren-api/search-api.log', 0, Logger::DEBUG));

                $handlerStack->push(
                    Middleware::log(
                        $logger,
                        new MessageFormatter(MessageFormatter::SHORT)
                    )
                );
            }

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['curatoren_api.base_url'],
                    'headers' => [
                        'Content-type' => 'application/json; charset=utf-8',
                        'Accept' => 'application/ld+json',
                    ],
                    'handler' => $handlerStack,
                ]
            );

            return new CuratorenClient($guzzleClient);
        };

        $pimple['curatoren_api_test'] = function (Container $pimple) {

            $curatorenClient = clone $pimple['curatoren_api'];

            $config = $curatorenClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['curatoren_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $config['headers'] = $headers;

            $curatorenClient->setClient(new \GuzzleHttp\Client($config));

            return $curatorenClient;
        };
    }
}
