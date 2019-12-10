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
    public function render(string $preferredLanguage = 'nl');

    /**
     * Is the given region empty?
     *
     * @param $region
     * @return bool
     */
    public function isRegionEmpty($region);

    /**
     * Get the widget to region mapping.
     *
     * @return array
     */
    public function getWidgetMapping();

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

    /**
     * Get a list of all widgets in this layout.
     * @return WidgetTypeInterface[]
     */
    public function getWidgets();

    /**
     * Get the index of last widget.
     * @return mixed
     */
    public function getLastWidgetIndex();

    /**
     * Add a widget to the given region.
     * @param $region
     * @param $widget
     */
    public function addWidget($region, $widget);

    /**
     * Remove the given widget.
     *
     * @param $widget
     * @return mixed
     */
    public function removeWidget($widget);
}
