<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Defines an interface to add widget types.
 */
interface WidgetTypeInterface extends \JsonSerializable
{

    /**
     * Get the id of the widget.
     *
     * @return string
     */
    public function getId();

    /**
     * Render the widget.
     * @param $cdbid
     * @return string
     */
    public function render($cdbid = '', $preferredLanguage = 'nl');

    /**
     * Render the widget placeholder, when a page is loaded.
     *
     * @return string
     */
    public function renderPlaceholder();
}
