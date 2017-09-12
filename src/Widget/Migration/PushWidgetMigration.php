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
     * @param $legacySettings
     */
    public function __construct($legacySettings)
    {
        $name = 'tips-1';
        $type = 'tips';

        $settings = [];

        parent::__construct($settings, $name, $type);
    }

}
