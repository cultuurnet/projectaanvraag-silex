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
                        'X-Api-Key' => $pimple['search_api.api_key'],
                    ],
                    'handler' => $handlerStack,
                ]
            );

            return new SearchClient($guzzleClient, new Serializer());
        };

        $pimple['search_api_test'] = function (Container $pimple) {

            $searchClient = clone $pimple['search_api'];

            $config = $searchClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['search_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $headers['X-Api-Key'] = $pimple['search_api_test.api_key'];
            $config['headers'] = $headers;

            $searchClient->setClient(new \GuzzleHttp\Client($config));

            return $searchClient;
        };
    }
}
