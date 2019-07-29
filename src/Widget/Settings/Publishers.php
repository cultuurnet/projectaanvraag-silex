<?php

namespace CultuurNet\ProjectAanvraag\Widget\Settings;

/**
 * Provides a publishers type
 */
class Publishers
{

    /**
     * Cleanup the configuration.
     */
    public function cleanup($configuration)
    {
        if (isset($configuration) && is_array($configuration)) {
            return $configuration;
        }
    }
}
