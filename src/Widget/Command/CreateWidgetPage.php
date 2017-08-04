<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

/**
 * Class CreateWidgetPage
 * @package CultuurNet\ProjectAanvraag\Widget\Command
 */
class CreateWidgetPage extends WidgetCommand
{
    /**
     * @var WidgetPageInterface
     */
    protected $widgetPage;

    /**
     * CreateWidgetPage constructor.
     *
     * @param WidgetPageInterface $widgetPage
     */
    public function __construct(WidgetPageInterface $widgetPage)
    {
        $this->widgetPage = $widgetPage;

        parent::__construct($widgetPage);
    }

    /**
     * Mark widget page as draft version
     */
    public function setAsDraft()
    {
        $this->widgetPage->setAsDraft();
    }

    /**
     * @param $userID
     */
    public function setCreatedByUser($userID)
    {
        $this->widgetPage->setCreatedByUser($userID);
    }

    /***
     * @param $userID
     */
    public function setLastUpdatedUser($userID)
    {
        $this->widgetPage->setLastUpdatedByUser($userID);
    }
}
