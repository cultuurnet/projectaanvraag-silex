<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\ContainerFactoryPluginInterface;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use Pimple\Container;

class WidgetTypeBase implements WidgetTypeInterface, ContainerFactoryPluginInterface
{

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    protected $renderer;

    /**
     * LayoutBase constructor.
     *
     * @param \Twig_Environment $twig
     * @param RendererInterface $renderer
     * @param array $configuration
     */
    public function __construct(\Twig_Environment $twig, RendererInterface $renderer, array $configuration)
    {
        $this->renderer = $renderer;
        $this->twig = $twig;
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $configuration)
    {
        return new static(
            $container['twig'],
            $container['widget_renderer'],
            $configuration
        );
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function renderPlaceholder()
    {
        return '';
    }
}
