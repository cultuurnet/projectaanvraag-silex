<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Provides an interface for widget row layouts.
 */
interface LayoutInterface
{

    /**
     * Render the current layout.
     * @return string
     */
    public function render();
}
