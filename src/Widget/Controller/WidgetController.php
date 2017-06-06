<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use Doctrine\MongoDB\Connection;

/**
 * Provides a controller to render widget pages and widgets.
 */
class WidgetController
{

    /**
     * @var RendererInterface
     */
    protected $renderer;

    public function __construct(RendererInterface $renderer, Connection $db)
    {
        $this->renderer = $renderer;
        $db->connect();
    }

    public function renderPage() {

    }

    public function renderWidget() {

    }

}