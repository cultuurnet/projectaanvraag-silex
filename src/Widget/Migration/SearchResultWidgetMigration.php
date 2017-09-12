<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class SearchResultWidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
class SearchResultWidgetMigration extends WidgetMigration
{
    /**
     * WidgetMigration constructor.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        $name = 'search-results-1';
        $type = 'search-results';
        parent::__construct($settings, $name, $type);
    }

}
