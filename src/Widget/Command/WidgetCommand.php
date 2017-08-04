<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use JMS\Serializer\Annotation\Type;

/**
 * Class WidgetCommand
 * @package CultuurNet\ProjectAanvraag\Widget\Command
 */
abstract class WidgetCommand
{
    /**
     * @var WidgetPageInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\WidgetPage")
     */
    private $widgetPage;

    /**
     * DeleteProject constructor.
     *
     * @param WidgetPageInterface $widgetPage
     */
    public function __construct($widgetPage)
    {
        $this->widgetPage = $widgetPage;
    }

    /**
     * @return WidgetPageInterface
     */
    public function getWidgetPage()
    {
        return $this->widgetPage;
    }

    /**
     * @param WidgetPageInterface $widgetPage
     *
     * @return WidgetCommand
     */
    public function setWidgetPage($widgetPage)
    {
        $this->widgetPage = $widgetPage;

        return $this;
    }
}
