<?php

namespace CultuurNet\ProjectAanvraag;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DiscoveryBase implements DiscoveryInterface
{

    /**
     * @var AnnotationReader
     */
    protected $annotationReader;

    /**
     * Info to use for discovering.
     * Every info item should contain a namespace and a path.
     * @var array
     */
    protected $discoveryInfo = [];

    /**
     * The found definitions.
     * @var array
     */
    protected $definitions;

    /**
     * Namespace to use.
     * @var string
     */
    protected $namespace;

    /**
     * Cache index to use.
     * @var string
     */
    protected $cache_index;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * DiscoveryBase constructor.
     */
    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Set the cache to use.
     * @param Cache $cache
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        $this->annotationReader = new CachedReader(new AnnotationReader(), $this->cache);
    }

    /**
     * {@inheritdoc}
     */
    public function discoverDefinitions()
    {

        if (isset($this->definitions)) {
            return;
        }

        if ($this->cache->contains($this->cache_index)) {
            $this->definitions = $this->cache->fetch($this->cache_index);
        } else {

            $this->definitions = [];
            foreach ($this->discoveryInfo as $info) {
                $finder = new Finder();
                $finder->files()->in($info['path']);

                /** @var SplFileInfo $file */
                foreach ($finder as $file) {
                    $className = $info['namespace'] . '\\' . $file->getBasename('.php');
                    if (class_exists($className)) {
                        $annotation = $this->annotationReader->getClassAnnotation(new \ReflectionClass($className), $this->namespace);
                        if (!$annotation) {
                            continue;
                        }

                        $this->definitions[$annotation->getId()] = [
                            'class' => $className,
                            'annotation' => $annotation,
                        ];
                    }
                }
            }

            $this->cache->save($this->cache_index, $this->definitions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function register($path, $namespace)
    {
        $this->discoveryInfo[] = [
            'path' => $path,
            'namespace' => $namespace,
        ];
    }

    /**
     * * {@inheritdoc}
     */
    public function getDefinitions()
    {
        $this->discoverDefinitions();
        return $this->definitions;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition($id)
    {
        $this->discoverDefinitions();
        return $this->definitions[$id] ?? [];
    }
}
