<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;

/**
 * Defines an interface for rendering a widget page.
 */
interface RendererInterface
{

    /**
     * Add a list of settings to the settings object.
     *
     * @param array $settings
     *   Settings to add.
     */
    public function addSettings(array $settings);

    /**
     * Renders a given page.
     *
     * @param WidgetPageInterface $widgetPage
     *     The widget page to render.
     * @return string
     */
    public function renderPage(WidgetPageInterface $widgetPage);

    /**
     * Set the active project.
     *
     * @param ProjectInterface $project
     */
    public function setProject(ProjectInterface $project);

    /**
     * Renders a given widget.
     *
     * @param WidgetTypeInterface $widgetType
     *     The widget to render.
     * @param ProjectInterface $project
     *     The project to use to render this widget.
     * @param String $cdbid
     *     The cdbid to use to render this widget.
     *
     * @return string
     */
    public function renderWidget(WidgetTypeInterface $widgetType, $cdbid = '', string $preferedLanguage = 'nl');

    /**
     * Renders a detail page for the given widget.
     *
     * @param SearchResults $searchResultsWidget
     * @return mixed
     */
    public function renderDetailPage(SearchResults $searchResultsWidget);

    /**
     * Attach a javascript file to the renderer.
     * @param $value
     *   Path to the file or inline js to be printed.
     * @param $type
     *   Type of js (inline or file)
     * @param int $weight
     *   Weight of the js file to define the order.
     */
    public function attachJavascript($value, $type = 'file', $weight = 0);

    /**
     * Attach a css file to the renderer.
     * @param $path
     *   Path to the file.
     * @param int $weight
     *   Weight of the js file to define the order.
     */
    public function attachCss($path, $weight = 0);

    /**
     * Get the attached javascript.
     *
     * @return array
     */
    public function getAttachedJs();

    /**
     * Get the attached css.
     *
     * @return array
     */
    public function getAttachedCss();
}
