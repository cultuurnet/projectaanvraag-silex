<?php

namespace CultuurNet\ProjectAanvraag\Widget;
use Symfony\Component\Routing\Generator\UrlGenerator;

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
     * @var array
     */
    private $settings = [];

    /**
     * @inheritDoc
     */
    public function addSettings(array $settings) {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * @inheritDoc
     */
    public function renderPage(WidgetPageInterface $widgetPage)
    {

        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/widgets.js');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/settings-loader.js');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/placeholder-load.js');

        $output = '';

        $widgetMapping = [];
        $rows = $widgetPage->getRows();
        foreach ($rows as $row) {

            $widgetIds = $row->getWidgetIds();
            foreach ($widgetIds as $widgetId) {
                $widgetMapping[$widgetId] = $widgetPage->getId();
            }

            $output .= $row->render();
        }

        $this->addSettings(['widgetMapping' => $widgetMapping]);

        $this->attachJavascript('CultuurnetWidgets.loadSettings(' . json_encode($this->settings) . ')', 'inline');

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function renderWidget(WidgetTypeInterface $widgetType)
    {
        return $widgetType->render();
    }

    /**
     * @inheritDoc
     */
    public function attachJavascript($value, $type = 'file', $weight = 0)
    {
        $this->jsFiles[] = [
            'value' => $value,
            'type' => $type,
            'weight' => $weight
        ];
    }

    /**
     * @inheritDoc
     */
    public function attachCss($path, $weight = 0)
    {
        $this->cssFiles[] = [
            'path' => $path,
            'weight' => $weight
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttachedJs()
    {
        uasort($this->jsFiles, [$this, 'sortByWeight']);
        return $this->jsFiles;
    }

    /**
     * @inheritDoc
     */
    public function getAttachedCss()
    {
        uasort($this->cssFiles, [$this, 'sortByWeight']);
        return $this->cssFiles;
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
