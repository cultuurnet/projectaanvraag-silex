<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

class CreateWidgetPage extends WidgetCommand
{

    protected $widgetPage;

    public function __construct(WidgetPageInterface $widgetPage)
    {
        $this->widgetPage = $widgetPage;

        parent::__construct($widgetPage);
    }

    public function setAsDraft()
    {
        $this->widgetPage->setAsDraft();
    }

    public function setCreatedByUser($userID)
    {
        $this->widgetPage->setCreatedByUser($userID);
    }

    public function setLastUpdatedUser($userID)
    {
        $this->widgetPage->setLastUpdatedByUser($userID);
    }
}
