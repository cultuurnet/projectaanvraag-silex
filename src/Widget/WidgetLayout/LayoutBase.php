<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use Pimple\Container;

/**
 * Base class for layouts.
 */
abstract class LayoutBase implements LayoutInterface, ContainerFactoryPluginInterface
{

    /**
     * @var array
     */
    protected $regions;

    /**
     * @var WidgetPluginManager
     */
    protected $widgetManager;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * LayoutBase constructor.
     *
     * @param $configuration
     * @param WidgetPluginManager $widgetManager
     */
    public function __construct(WidgetPluginManager $widgetManager, \Twig_Environment $twig, array $configuration)
    {
        $this->widgetManager = $widgetManager;
        $this->twig = $twig;
        $this->parseRegions($configuration['regions']);
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $configuration)
    {
        return new static(
            $container['widget_type_manager'],
            $container['twig'],
            $configuration
        );
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
                $this->regions[$regionId]['widgets'][] = $this->widgetManager->createInstance($widget['type'], $widget['settings']);
            }
        }
    }

    /**
     * Render the given region.
     * @param $regionName
     *   Region to render.
     */
    protected function renderRegion($regionName)
    {

        $content = '';
        if (isset($this->regions[$regionName], $this->regions[$regionName]['widgets'])) {
            foreach ($this->regions[$regionName]['widgets'] as $widget) {
                $content .= $widget->renderPlaceholder();
            }
        }

        return $content;
    }
}
