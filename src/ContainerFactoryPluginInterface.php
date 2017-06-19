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
     * @param array $configuration
     *   Configuration for the plugin.
     *
     * @return static Returns an instance of this plugin.
     *   Returns an instance of this plugin.
     */
    public static function create(Container $container, array $configuration);
}
