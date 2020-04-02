<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\CulturefeedHttpGuzzle\HttpClient;
use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Guzzle\Http\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Provides the different caching systems.
 */
class CacheProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {
        $pimple['cache_doctrine_filesystem'] = function (Container $pimple) {
            return new FilesystemCache($pimple['cache_directory'] . '/doctrine');
        };

        $pimple['cache_doctrine_redis'] = function (Container $pimple) {

            $redis = new \Redis();
            $redis->connect($pimple['cache.redis']['host'], $pimple['cache.redis']['port']);

            $redisCache = new RedisCache();
            $redisCache->setRedis($redis);

            return $redisCache;
        };

        $pimple['annotation_cache'] = function (Container $pimple) {

            if ($pimple['cache.annotations']['enabled']) {
                return $pimple['cache_doctrine_' . $pimple['cache.annotations']['backend']];
            }
        };

        $pimple['odm_orm_cache'] = function (Container $pimple) {

            if (!$pimple['cache.odm_orm']['enabled']) {
                return null;
            }

            $settings = [
                'driver' => $pimple['cache.odm_orm']['backend'],
            ];

            switch ($pimple['cache.odm_orm']['backend']) {
                case 'filesystem':
                    $settings['path'] = $pimple['cache_directory'] . '/odm-orm';
                    break;

                case 'redis':
                    $settings += $pimple['cache.redis'];
                    break;
            }

            return $settings;
        };
    }
}
