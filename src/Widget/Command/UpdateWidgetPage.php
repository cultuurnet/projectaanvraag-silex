<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

/**
 * Provides a command to update a widget.
 */
class UpdateWidgetPage extends WidgetCommand
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
