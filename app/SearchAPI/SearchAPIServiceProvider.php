<?php

namespace CultuurNet\ProjectAanvraag\SearchAPI;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\Serializer\Serializer;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
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

            return new SearchClient($client, new Serializer());
        };
    }
}
