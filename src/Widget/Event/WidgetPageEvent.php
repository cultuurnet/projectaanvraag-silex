<?php

namespace CultuurNet\ProjectAanvraag\Widget\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use JMS\Serializer\Annotation\Type;

/**
 * Provides an abstract class for widget page events.
 */
abstract class WidgetPageEvent extends AbstractRetryableMessage
{
    /**
     * @var WidgetPageInterface
     * @Type("CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity")
     */
    private $widgetPage;

    /**
     * WidgetPage constructor.
     *
     * @param WidgetPageInterface $widgetPage
     * @param int $delay
     */
    public function __construct(WidgetPageInterface $widgetPage, $delay = 0)
    {
        $this->widgetPage = $widgetPage;
        $this->delay = $delay;
    }

    /**
     * @return WidgetPageInterface
     */
    public function getWidgetPage()
    {
        return $this->widgetPage;
    }

    /**
     * @param $widgetPage
     *
     * @return WidgetPageInterface
     */
    public function setWidgetPage($widgetPage)
    {
        $this->widgetPage = $widgetPage;

        return $this;
    }
}
