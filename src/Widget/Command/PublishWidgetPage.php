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
    public function setAsPublished()
    {
        $this->widgetPage->setAsPublished();
    }

    /***
     * @param $userID
     */
    public function setLastUpdatedUser($userID)
    {
        $this->widgetPage->setLastUpdatedByUser($userID);
    }
}
