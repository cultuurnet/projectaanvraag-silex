<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinkerAPI;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\ProjectAanvraag\ArticleLinker\ArticleLinkerClient;
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

class ArticleLinkerAPIServiceProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {

        $pimple['articlelinker_api'] = function (Container $pimple) {

            $handlerStack = HandlerStack::create();

            if ($pimple['articlelinker_api.cache.enabled']) {
                $handlerStack->push(
                    new CacheMiddleware(
                        new PrivateCacheStrategy(
                            new DoctrineCacheStorage(
                                $pimple['cache_doctrine_' . $pimple['articlelinker_api.cache.backend']]
                            )
                        )
                    ),
                    'cache'
                );
            }

            if ($pimple['debug']) {
                $logger = new Logger('articlelinker_api');
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
                $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/article-linker-api/article-linker-api.log', 0, Logger::DEBUG));

                $handlerStack->push(
                    Middleware::log(
                        $logger,
                        new MessageFormatter(MessageFormatter::SHORT)
                    )
                );
            }

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['articlelinker_api.base_url'],
                    'headers' => [
                        'Content-type' => 'application/json; charset=utf-8',
                        'Accept' => 'application/ld+json',
                    ],
                    'handler' => $handlerStack,
                ]
            );

            return new ArticleLinkerClient($guzzleClient);
        };

        $pimple['articlelinker_api_test'] = function (Container $pimple) {

            $articleLinkerClient = clone $pimple['articlelinker_api'];

            $config = $articleLinkerClient->getClient()->getConfig();
            $config['base_uri'] = $pimple['articlelinker_api_test.base_url'];
            $headers = $config['headers'] ?? [];
            $config['headers'] = $headers;

            $articleLinkerClient->setClient(new \GuzzleHttp\Client($config));

            return $articleLinkerClient;
        };

        $pimple['cache_repository'] = function (Container $pimple) {
            return $pimple['orm.em']->getRepository('ProjectAanvraag:Cache');
        };
    }
}
