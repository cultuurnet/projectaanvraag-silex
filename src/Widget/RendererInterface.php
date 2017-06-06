<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * Defines an interface for rendering a widget page.
 */
interface RendererInterface
{

    /**
     * Renders a given page.
     *
     * @param WidgetPageInterface $widgetPage
     *     The widget page to render.
     * @return string
     */
    public function renderPage(WidgetPageInterface $widgetPage);

    /**
     * Renders a given widget.
     *
     * @param WidgetTypeInterface $widgetType
     *     The widget to render.
     *
     * @return string
     */
    public function renderWidget(WidgetTypeInterface $widgetType);

}