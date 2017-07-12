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
     * Creates a pre-configured instance of a layout or widget.
     * @param $id
     *   ID of the plugin to load
     * @param array $configuration
     *   Configuration for the plugin.
     * @param bool $cleanup
     *   Cleanup the configuration or not.
     * @return
     */
    public function createInstance($id, $configuration = [], $cleanup = FALSE)
    {
        $definition = $this->getDefinition($id);

        if (is_subclass_of($definition['class'], 'CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface')) {
            return $definition['class']::create($this->container, $definition, $configuration, $cleanup);
        }

        $class = $definition['class'];
        return new $class($definition, $configuration, $cleanup);
    }

    /**
     * Get a definition.
     */
    public function getDefinition($id)
    {
        $definition = $this->discovery->getDefinition($id);
        if ($definition) {
            return $definition;
        }

        throw new WidgetPluginNotFoundException('The ' . $id . ' plugin does not exist');
    }
}
