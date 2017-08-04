<?php

namespace CultuurNet\ProjectAanvraag\Widget\Event;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

/**
 * Class WidgetPageUpdated
 * @package CultuurNet\ProjectAanvraag\Widget\Event
 */
class WidgetPageUpdated extends WidgetPageEvent
{

    /**
     * @var WidgetPageInterface
     */
    protected $newWidgetPage;

    /**
     * UpdateWidgetPage constructor.
     *
     * @param WidgetPageInterface $newWidgetPage
     * @param WidgetPageInterface $existingWidgetPage
     */
    public function __construct(WidgetPageInterface $newWidgetPage, WidgetPageInterface $existingWidgetPage)
    {
        parent::__construct($existingWidgetPage);
        $this->newWidgetPage = $newWidgetPage;
    }

    /**
     * @return WidgetPageInterface
     */
    public function getNewWidgetPage()
    {
        return $this->newWidgetPage;
    }

}
