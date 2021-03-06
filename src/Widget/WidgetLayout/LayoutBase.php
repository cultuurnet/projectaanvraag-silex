<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use Pimple\Container;

/**
 * Base class for layouts.
 */
abstract class LayoutBase implements LayoutInterface, ContainerFactoryPluginInterface
{

    /**
     * @var array
     */
    protected $pluginDefinition;

    /**
     * @var array
     */
    protected $regions = [];

    /**
     * Mapping of all widgets in this layout.
     * @var array
     */
    protected $widgetMapping = [];

    /**
     * Flat list of all widgets in this layout.
     * @var WidgetType[]
     */
    protected $widgets;

    /**
     * @var WidgetPluginManager
     */
    protected $widgetManager;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Cleanup the configuration options or not.
     * @var bool
     */
    protected $cleanup;

    /**
     * The last index that was used.
     * @var int
     */
    protected $lastWidgetIndex;

    /**
     * LayoutBase constructor.
     *
     * @param array $pluginDefinition
     * @param WidgetPluginManager $widgetManager
     * @param \Twig_Environment $twig
     * @param array $configuration
     * @param bool $cleanup
     */
    final public function __construct(array $pluginDefinition, WidgetPluginManager $widgetManager, \Twig_Environment $twig, array $configuration, bool $cleanup)
    {
        $this->pluginDefinition = $pluginDefinition;
        $this->widgetManager = $widgetManager;
        $this->twig = $twig;
        $this->cleanup = $cleanup;
        $this->lastWidgetIndex = $configuration['lastIndex'] ?? -1;

        if (isset($configuration['regions'])) {
            $this->parseRegions($configuration['regions']);
        }
    }

    final public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $container['widget_type_manager'],
            $container['twig'],
            $configuration,
            $cleanup
        );
    }

    public function getLastWidgetIndex()
    {
        return $this->lastWidgetIndex;
    }

    public function getWidgetMapping()
    {
        return $this->widgetMapping;
    }

    /**
     * Parse the given region content to widgets.
     * @param $regions
     */
    protected function parseRegions($regions)
    {

        foreach ($regions as $regionId => $region) {
            if (!isset($this->regions[$regionId])) {
                $this->regions[$regionId]['widgets'] = [];
            }

            foreach ($region['widgets'] as $widget) {
                $this->lastWidgetIndex++;

                $this->widgetMapping[$widget['id']] = $regionId;
                $this->regions[$regionId]['widgets'][$widget['id']] = $this->widgetManager->createInstance($widget['type'], $widget, $this->cleanup);
                $this->regions[$regionId]['widgets'][$widget['id']]->setIndex($this->lastWidgetIndex);
            }
        }
    }


    /**
     * Render the given region.
     * @param $regionName
     *   Region to render.
     */
    protected function renderRegion($regionName, $preferredLanguage = 'nl')
    {

        $content = '';
        if (isset($this->regions[$regionName], $this->regions[$regionName]['widgets'])) {
            foreach ($this->regions[$regionName]['widgets'] as $widget) {
                $content .= $widget->renderPlaceholder($preferredLanguage);
            }
        }

        return $content;
    }

    public function isRegionEmpty($region)
    {
        return empty($this->regions[$region]) || empty($this->regions[$region]['widgets']);
    }

    public function hasWidget($widgetId)
    {
        return isset($this->widgetMapping[$widgetId]);
    }

    public function getWidget($widgetId)
    {
        $region = $this->widgetMapping[$widgetId] ?? '';
        return $this->regions[$region]['widgets'][$widgetId] ?? null;
    }

    public function getWidgetIds()
    {
        return array_keys($this->widgetMapping);
    }

    public function getWidgets()
    {
        $widgets = [];
        foreach ($this->widgetMapping as $widgetId => $region) {
            $widgets[$widgetId] = $this->getWidget($widgetId);
        }
        return $widgets;
    }

    public function addWidget($region, $widget)
    {
        $this->regions[$region]['widgets'][] = $widget;
    }

    public function removeWidget($widget)
    {
        if (isset($this->widgetMapping[$widget->getId()])) {
            $region = $this->widgetMapping[$widget->getId()];

            foreach ($this->regions[$region]['widgets'] as $key => $widgetInRegion) {
                if ($widgetInRegion->getId() === $widget->getId()) {
                    unset($this->regions[$region]['widgets'][$key]);
                }
            }

            unset($this->widgetMapping[$widget->getId()]);
        }
    }

    public function jsonSerialize()
    {
        $regions = [];
        foreach ($this->regions as $regionId => $region) {
            $regions[$regionId]['widgets'] = [];
            /** @var WidgetTypeInterface $widget */
            foreach ($region['widgets'] as $widget) {
                $regions[$regionId]['widgets'][] = $widget->jsonSerialize();
            }
        }

        return [
            'type' => $this->pluginDefinition['annotation']->getId(),
            'regions' => $regions,
        ];
    }
}
