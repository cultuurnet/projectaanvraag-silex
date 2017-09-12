<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class HtmlWidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class HtmlWidgetMigration extends WidgetMigration
{
    /**
     * WidgetMigration constructor.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        $name = 'html-1';
        $type = 'html';
        parent::__construct($settings, $name, $type);
    }

}
