<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class PushWidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class PushWidgetMigration extends WidgetMigration
{
    /**
     * WidgetMigration constructor.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        $name = 'tips-1';
        $type = 'tips';
        parent::__construct($settings, $name, $type);
    }

}
