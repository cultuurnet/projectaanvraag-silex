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
     * @param $legacySettings
     */
    public function __construct($legacySettings)
    {
        $name = 'search-results-1';
        $type = 'search-results';

        $settings = [];

        parent::__construct($settings, $name, $type);
    }

}
