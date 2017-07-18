<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides an interface for widget row layouts.
 */
interface LayoutInterface extends \JsonSerializable
{

    /**
     * Render the current layout.
     * @return string
     */
    public function render();
}
