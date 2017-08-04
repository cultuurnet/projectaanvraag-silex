<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

/**
 * Class PublishWidgetPage
 * @package CultuurNet\ProjectAanvraag\Widget\Command
 */
class PublishWidgetPage extends WidgetCommand
{
    /**
     * PublishWidgetPage constructor.
     *
     * @param WidgetPageInterface $widgetPage
     */
    public function __construct(WidgetPageInterface $widgetPage)
    {
        parent::__construct($widgetPage);
    }
}
