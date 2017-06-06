<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Defines an interface to add widget types.
 */
interface WidgetTypeInterface
{

    /**
     * Render the widget.
     *
     * @return string
     */
    public function render();

    /**
     * Render the widget when a page is being rendered.
     *
     * @return string
     */
    public function pageRender();

    /**
     * Returns the list of javascript dependencies for this widget.
     *
     * @return array
     */
    public function getRequiredJs();

    /**
     * Returns the list of css dependencies for this widget.
     *
     * @return array
     */
    public function getRequiredCss();

}