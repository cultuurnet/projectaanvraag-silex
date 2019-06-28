<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinkerAPI;

use CultuurNet\ProjectAanvraag\APIServiceProviderBase;
use CultuurNet\ProjectAanvraag\ArticleLinker\ArticleLinkerClient;
use Doctrine\Common\Cache\CacheProvider;
use GuzzleHttp\Client;
use Pimple\Container;
use Symfony\Component\Cache\Simple\DoctrineCache;

class ArticleLinkerAPIServiceProvider extends APIServiceProviderBase
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {

        $pimple['articlelinker_api'] = function (Container $pimple) {

            $guzzleClient = new Client(
                [
                    'base_uri' => $pimple['articlelinker_api.base_url'],
                    'headers' => [
                        'Content-type' => 'application/json; charset=utf-8',
                        'Accept' => 'application/ld+json',
                    ],
                    'handler' => $this->getHandlerStack('articlelinker_api', $pimple),
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

        $pimple['articlelinker_cache'] = function (Container $pimple) {
            if ($pimple['articlelinker_api.cache.enabled']) {
                /** @var CacheProvider $cache */
                $cache = new DoctrineCache(
                    $pimple['cache_doctrine_' . $pimple['articlelinker_api.cache.backend']],
                    'article-linker',
                    $pimple['articlelinker_api.cache.ttl']
                );
                return $cache;
            }
        };
    }
}
