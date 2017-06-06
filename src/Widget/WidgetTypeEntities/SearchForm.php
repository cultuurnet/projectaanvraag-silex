<?php

use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

/**
 * Provides the search form widget.
 */
class SearchForm implements WidgetTypeInterface
{
    /**
     * @inheritDoc
     */
    public function render()
    {
        // TODO: Implement render() method.
    }

    /**
     * @inheritDoc
     */
    public function pageRender()
    {
        // TODO: Implement pageRender() method.
    }

    /**
     * @inheritDoc
     */
    public function getRequiredJs()
    {
        // Example. Source directory should move to a config setting and injected into class.  Required should live in properties.
        return [
            __DIR__ . '/../../../assets/search-form/extra-js.js'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getRequiredCss()
    {
        // Example. Source directory should move to a config setting and injected into class. Required should live in properties.
        return [
            __DIR__ . '/../../../assets/search-form/extra-css.css'
        ];
    }
}