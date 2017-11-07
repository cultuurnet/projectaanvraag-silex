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
        $type = 'search-form';

        $settings = [];

        // what
        if (isset($legacySettings['control_what']['fields'])) {
            // what enabled
            $settings['fields']['type']['keyword_search']['enabled'] = $legacySettings['control_what']['fields']['q']['enabled'];
            // what label
            $settings['fields']['type']['keyword_search']['label'] = $legacySettings['control_what']['fields']['q']['label'];
            // what placeholder
            $settings['fields']['type']['keyword_search']['placeholder'] = $legacySettings['control_what']['fields']['q']['placeholder'] ?? '';
        }

        // where
        if (isset($legacySettings['control_where']['fields'])) {
            // where enabled
            $settings['fields']['location']['keyword_search']['enabled'] = $legacySettings['control_where']['fields']['city']['enabled'];
            // where label
            $settings['fields']['location']['keyword_search']['label'] = $legacySettings['control_where']['fields']['city']['label'];
            // where placeholder
            $settings['fields']['location']['keyword_search']['placeholder'] = $legacySettings['control_where']['fields']['location']['placeholder'] ?? '';
        }
        // when
        if (isset($legacySettings['control_when']['fields'])) {
            // when enabled
            $settings['fields']['time']['date_search']['enabled'] = $legacySettings['control_when']['fields']['datetype']['enabled'];
            // when label
            $settings['fields']['time']['date_search']['label'] = $legacySettings['control_when']['fields']['datetype']['label'];
            // when placeholder
            $settings['fields']['time']['date_search']['placeholder'] = $legacySettings['control_when']['fields']['datetype']['placeholder'] ?? '';
            // when options
            if (!empty($legacySettings['control_when']['fields']['datetype']['options'])) {
                // The other options all do not exist in the new builder.
                $options = array_flip($legacySettings['control_when']['fields']['datetype']['options']);
                $settings['fields']['time']['date_search']['options'] = [
                    'today' => (isset($options['today']) ? true : false),
                    'tomorrow' => (isset($options['tomorrow']) ? true : false),
                    'weekend' => (isset($options['thisweekend']) ? true : false),
                    'days_30' => (isset($options['next30days']) ? true : false),
                ];
                // when custom date option
                if (isset($legacySettings['control_when']['fields']['daterange']['enabled']) && $legacySettings['control_when']['fields']['daterange']['enabled']) {
                    $settings['fields']['time']['date_search']['options']['custom_date'] = true;
                }
            }
            // when default
            if (isset($legacySettings['control_when']['fields']['datetype']['default'])) {
                $defaultDate = 'placeholder';
                switch ($legacySettings['control_when']['fields']['datetype']['default']) {
                    case 'today':
                        $defaultDate = 'today';
                        break;
                    case 'tomorrow':
                        $defaultDate = 'tomorrow';
                        break;
                    case 'thisweekend':
                        $defaultDate = 'weekend';
                        break;
                    case 'next30days':
                        $defaultDate = 'days_30';
                        break;
                }
                $settings['fields']['time']['date_search']['default_option'] = $defaultDate;
            }
        }
        // url
        if (isset($legacySettings['url'])) {
            $settings['general']['destination'] = $legacySettings['url'];
        }
        // open in new window
        if (isset($legacySettings['new_window'])) {
            $settings['general']['new_window'] = $legacySettings['new_window'];
        }
        // parameters
        if (isset($legacySettings['parameters']['raw'])) {
            $settings['search_params']['query'] = $legacySettings['parameters']['raw'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $type);
    }
}
