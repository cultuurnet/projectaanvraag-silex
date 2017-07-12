<?php

namespace CultuurNet\ProjectAanvraag;

use Doctrine\Common\Annotations\AnnotationReader;
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
     * DiscoveryBase constructor.
     */
    public function __construct()
    {
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * {@inheritdoc}
     */
    public function discoverDefinitions()
    {

        if (isset($this->definitions)) {
            return;
        }

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
