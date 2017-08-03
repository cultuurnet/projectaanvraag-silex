<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\CulturefeedHttpGuzzle\HttpClient;
use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Guzzle\Http\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CacheProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        $pimple['cache_doctrine_filesystem'] = function (Container $pimple) {
            return new FilesystemCache($pimple['cache.file_system']['location']);
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
    }
}
