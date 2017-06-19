<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Exception thrown when a plugin was not found.
 */
class WidgetPluginNotFoundException extends \Exception
{

    /**
     * Construct an WidgetPluginNotFoundException exception.
     *
     * For the remaining parameters see \Exception.
     *
     * @param string $pluginId
     *   The plugin ID that was not found.
     *
     * @see \Exception
     */
    public function __construct($pluginId, $message = '', $code = 0, \Exception $previous = null)
    {
        if (empty($message)) {
            $message = sprintf("Plugin ID '%s' was not found.", $pluginId);
        }
        parent::__construct($message, $code, $previous);
    }
}
