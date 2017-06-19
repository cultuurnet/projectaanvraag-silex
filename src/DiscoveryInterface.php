<?php
/**
 * Created by PhpStorm.
 * User: nils
 * Date: 12/06/2017
 * Time: 16:39
 */

namespace CultuurNet\ProjectAanvraag;

/**
 * Base class for discoveries.
 */
interface DiscoveryInterface
{
    /**
     * Discover the definitions.
     */
    public function discoverDefinitions();

    /**
     * Register a new item to the discovery.
     */
    public function register($path, $namespace);

    /**
     * Get the definitions.
     * @return array
     */
    public function getDefinitions();
}
