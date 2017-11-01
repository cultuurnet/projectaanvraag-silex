<?php

namespace CultuurNet\ProjectAanvraag\SearchAPI;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\Serializer;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Log\MessageFormatter;
use Guzzle\Log\PsrLogAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
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
        $pimple['search_api_cache'] = function (Container $pimple) {
            return new CachePlugin(
                [
                    'storage' => new FixedTtlCacheStorage(
                        new DoctrineCacheAdapter(
                            $pimple['cache_doctrine_' . $pimple['search_api.cache.backend']]
                        )
                    ),
                ]
            );
        };

        $pimple['search_api'] = function (Container $pimple) {

            $client = new \Guzzle\Http\Client($pimple['search_api.base_url']);

            // Add search API key as default header.
            if (isset($pimple['config']['search_api']['api_key'])) {
                $searchApiKey = $pimple['config']['search_api']['api_key'];
                $headers = [
                    'X-Api-Key' => $searchApiKey,
                ];
                $client->setDefaultHeaders($headers);
            }

            if ($pimple['search_api.cache.enabled']) {
                $client->addSubscriber($pimple['search_api_cache']);
            }

            if ($pimple['debug']) {
                $logger = new Logger('search_api');
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/search-api/search-api.log', 0, Logger::DEBUG));

                $logAdapter = new PsrLogAdapter($logger);
                $logPlugin = new \Guzzle\Plugin\Log\LogPlugin(
                    $logAdapter,
                    MessageFormatter::SHORT_FORMAT
                );
                $client->addSubscriber($logPlugin);
            }

            return new SearchClient($client, new Serializer());
        };
    }
}
