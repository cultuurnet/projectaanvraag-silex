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

    /**
     * Returns true if the given widget id exists in the layout.
     *
     * @param $widgetId
     * @return bool
     */
    public function hasWidget($widgetId);

    /**
     * Get the widget with given id.
     * @param $widgetId
     * @return WidgetTypeInterface|null
     */
    public function getWidget($widgetId);

    /**
     * Get a list of all widget ids in this layout.
     * @return array
     */
    public function getWidgetIds();
}
