<?php

namespace CultuurNet\ProjectAanvraag;

use Pimple\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a common interface for dependency container injection.
 */
interface ContainerFactoryPluginInterface
{

    /**
     * Creates an instance of the plugin.
     *
     * @param Container $container
     *   The pimple container.
     * @param array $pluginDefinition
     *   The plugin definition.
     * @param array $configuration
     *   Configuration for the plugin.
     * @param bool $cleanup
     *   Cleanup configuration or not
     * @return static Returns an instance of this plugin.
     *   Returns an instance of this plugin.
     */
    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup);
}
