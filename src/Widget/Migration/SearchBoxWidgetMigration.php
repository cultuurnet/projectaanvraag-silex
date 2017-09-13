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

        // what
        if (isset($legacySettings['control_what']['fields'])) {
            // what enabled
            $settings['fields']['type']['keyword_search']['enabled'] = $legacySettings['control_what']['fields']['q']['enabled'];
            // what label
            $settings['fields']['type']['keyword_search']['label'] = $legacySettings['control_what']['fields']['q']['label'];
            // what placeholder
            $settings['fields']['type']['keyword_search']['placeholder'] = $legacySettings['control_what']['fields']['q']['placeholder'];
        }
        // where
        if (isset($legacySettings['control_where']['fields'])) {
            // where enabled
            $settings['fields']['location']['keyword_search']['enabled'] = $legacySettings['control_where']['fields']['location']['enabled'];
            // where label
            $settings['fields']['location']['keyword_search']['label'] = $legacySettings['control_where']['fields']['location']['label'];
        }
        // when
        if (isset($legacySettings['control_when']['fields'])) {
            // when enabled
            $settings['fields']['time']['date_search']['enabled'] = $legacySettings['control_when']['fields']['datetype']['enabled'];
            // when label
            $settings['fields']['time']['date_search']['label'] = $legacySettings['control_when']['fields']['datetype']['label'];
        }
        // url
        if (isset($legacySettings['url'])) {
            $settings['general']['destination'] = $legacySettings['url'];
        }
        // open in new window
        if (isset($legacySettings['new_window'])) {
            $settings['general']['new_window'] = $legacySettings['new_window'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $name, $type);
    }

}
