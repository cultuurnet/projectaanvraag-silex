<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

class PublishWidgetPage extends WidgetCommand
{

    public function __construct(WidgetPageInterface $widgetPage)
    {
        parent::__construct($widgetPage);
    }
}
