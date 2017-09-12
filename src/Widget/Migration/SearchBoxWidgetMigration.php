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
     * @param $legacySettings
     */
    public function __construct($legacySettings)
    {
        $name = 'zoekformulier-1';
        $type = 'search-form';

        $settings = [];

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $name, $type);
    }

}
