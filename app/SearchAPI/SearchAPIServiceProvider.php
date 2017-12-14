<?php

namespace CultuurNet\ProjectAanvraag\SearchAPI;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\Serializer;
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

class SearchAPIServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {

        $pimple['search_api'] = function (Container $pimple) {

            $client = new \Guzzle\Http\Client($pimple['search_api.base_url']);

            $handlerStack = HandlerStack::create();

            if ($pimple['search_api.cache.enabled']) {
                $handlerStack->push(
                    new CacheMiddleware(
                        new PrivateCacheStrategy(
                            new DoctrineCacheStorage(
                                $pimple['cache_doctrine_' . $pimple['search_api.cache.backend']]
                            )
                        )
                    ),
                    'cache'
                );
            }

            if ($pimple['debug']) {
                $logger = new Logger('search_api');
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/search-api/search-api.log', 0, Logger::DEBUG));

                $handlerStack->push(
                    Middleware::log(
                        $logger,
                        new MessageFormatter(MessageFormatter::SHORT)
                    )
                );
            }

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['search_api.base_url'],
                    'headers' => [
                        'X-Api-Key' => $pimple['config']['search_api']['api_key'],
                    ],
                    'handler' => $handlerStack,
                ]
            );

            return new SearchClient($guzzleClient, new Serializer());
        };
    }
}
