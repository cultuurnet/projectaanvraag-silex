<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
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
     * Cleanup the configuration options or not.
     * @var bool
     */
    protected $cleanup;

    /**
     * LayoutBase constructor.
     *
     * @param array $pluginDefinition
     * @param WidgetPluginManager $widgetManager
     * @param \Twig_Environment $twig
     * @param array $configuration
     * @param bool $cleanup
     */
    public function __construct(array $pluginDefinition, WidgetPluginManager $widgetManager, \Twig_Environment $twig, array $configuration, bool $cleanup)
    {
        $this->pluginDefinition = $pluginDefinition;
        $this->widgetManager = $widgetManager;
        $this->twig = $twig;
        $this->cleanup = $cleanup;

        if (isset($configuration['regions'])) {
            $this->parseRegions($configuration['regions']);
        }
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $container['widget_type_manager'],
            $container['twig'],
            $configuration,
            $cleanup
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
                $this->regions[$regionId]['widgets'][] = $this->widgetManager->createInstance($widget['type'], $widget['settings'], $this->cleanup);
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

    /**
     * {@inheritdoc}
     */
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
