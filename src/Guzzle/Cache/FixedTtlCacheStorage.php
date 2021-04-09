<?php

namespace CultuurNet\ProjectAanvraag\Guzzle\Cache;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Cache\DefaultCacheStorage;

/**
 * Provides a guzzle cache storage with a fixed ttl.
 */
class FixedTtlCacheStorage extends DefaultCacheStorage
{

    /**
     * The configured TTL.
     * @var int
     */
    protected $ttl;

    /**
     * @param mixed  $cache      Cache used to store cache data
     * @param string $keyPrefix  Provide an optional key prefix to prefix on all cache keys
     * @param int    $ttl TTL for the cache
     */
    public function __construct($cache, $keyPrefix = '', $ttl = 3600)
    {
        parent::__construct($cache, $keyPrefix, $ttl);
        $this->ttl = $ttl;
    }

    public function cache(RequestInterface $request, Response $response)
    {

        $request->getParams()->set('cache.override_ttl', $this->ttl);

        parent::cache($request, $response);
    }
}
