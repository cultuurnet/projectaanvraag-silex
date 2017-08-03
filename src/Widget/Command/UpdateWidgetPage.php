<?php

namespace CultuurNet\ProjectAanvraag\Widget\Command;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;

class UpdateWidgetPage extends WidgetCommand
{

    /**
     * @var WidgetPageInterface
     */
    protected $newWidgetPage;

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
