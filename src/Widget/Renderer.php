<?php

namespace CultuurNet\ProjectAanvraag\Widget;

/**
 * The rendered used to render widget pages or widget details in javascript.
 */
class Renderer implements RendererInterface
{

    /**
     * @var array
     */
    private $jsFiles = [];

    /**
     * @var array
     */
    private $cssFiles = [];

    /**
     * Renderer constructor.
     */
    public function __construct()
    {
        $this->attachJavascript(__DIR__ . '/../../web/assets/js/widgets/core/widgets.js');
    }

    /**
     * @inheritDoc
     */
    public function renderPage(WidgetPageInterface $widgetPage)
    {

        $output = '';

        $rows = $widgetPage->getRows();
        foreach ($rows as $row) {
            $output .= $row->render();
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function renderWidget(WidgetTypeInterface $widgetType)
    {
        // TODO: Implement renderWidget() method.
    }

    /**
     * @inheritDoc
     */
    public function attachJavascript($path, $weight = 0)
    {
        $this->jsFiles[$path] = $weight;
    }

    /**
     * @inheritDoc
     */
    public function attachCss($path, $weight = 0)
    {
        $this->cssFiles[$path] = $weight;
    }

    /**
     * @inheritDoc
     */
    public function getAttachedJs()
    {
        uasort($this->jsFiles, [$this, 'sortByWeight']);
        return array_keys($this->jsFiles);
    }

    /**
     * @inheritDoc
     */
    public function getAttachedCss()
    {
        uasort($this->cssFiles, [$this, 'sortByWeight']);
        return array_keys($this->cssFiles);
    }

    /**
     * Sort the array by weight.
     */
    public function sortByWeight($a, $b)
    {

        $aWeight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
        $bWeight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;
        if ($aWeight == $bWeight) {
            return 0;
        }
        return ($aWeight < $bWeight) ? -1 : 1;
    }
}
