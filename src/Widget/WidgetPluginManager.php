<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\DiscoveryInterface;
use Pimple\Container;

/**
 * General plugin manager for widgets project.
 */
class WidgetPluginManager
{

    /**
     * @var DiscoveryInterface
     */
    protected $discovery;

    /**
     * @var array
     */
    protected $definitions;

    /**
     * @var Container
     */
    protected $container;

    /**
     * WidgetPluginManager constructor.
     * @param DiscoveryInterface $discovery
     */
    public function __construct(DiscoveryInterface $discovery, Container $container)
    {
        $this->discovery = $discovery;
        $this->container = $container;
    }

    /**
     * Return all definitions.
     */
    public function getDefinitions()
    {
        if (!isset($this->definitions)) {
            $this->definitions = $this->discovery->getDefinitions();
        }

        return $this->definitions;
    }

    /**
     * Creates a pre-configured instance of a layout.
     */
    public function createInstance($id, $configuration = [])
    {
        $definition = $this->getDefinition($id);

        if (is_subclass_of($definition['class'], 'CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface')) {
            return $definition['class']::create($this->container, $configuration);
        }

        $class = $definition['class'];
        return new $class($configuration);
    }

    /**
     * Get a definition.
     */
    public function getDefinition($id)
    {
        $definitions = $this->getDefinitions();
        if (isset($definitions[$id])) {
            return $definitions[$id];
        }

        throw new WidgetPluginNotFoundException('The ' . $id . ' plugin does not exist');
    }
}
