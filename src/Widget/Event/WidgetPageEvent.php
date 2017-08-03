<?php

namespace CultuurNet\ProjectAanvraag\Widget\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use JMS\Serializer\Annotation\Type;

abstract class WidgetPageEvent extends AbstractRetryableMessage
{
  /**
   * @var WidgetPageEntity
   * @Type("CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity")
   */
    private $widgetPage;

  /**
   * WidgetPage constructor.
   * @param WidgetPageEntity $widgetPage
   * @param int $delay
   */
    public function __construct($project, $delay = 0)
    {
        $this->project = $project;
        $this->delay = $delay;
    }

  /**
   * @return WidgetPageEntity
   */
    public function getWidgetPage()
    {
        return $this->widgetPage;
    }

  /**
   * @param $widgetPage
   *
   * @return WidgetPageEvent
   */
    public function setWidgetPage($widgetPage)
    {
        $this->$widgetPage = $widgetPage;
        return $this;
    }
}
