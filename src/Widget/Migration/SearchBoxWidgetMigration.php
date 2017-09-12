<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class SearchBoxWidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class SearchBoxWidgetMigration extends WidgetMigration
{
    /**
     * WidgetMigration constructor.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        $name = 'zoekformulier-1';
        $type = 'search-form';
        parent::__construct($settings, $name, $type);
    }

}
