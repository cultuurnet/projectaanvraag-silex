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
        $type = 'search-results';

        $settings = [];

        // Add generic fields settings;
        if (isset($legacySettings['control_results']['visual']['results']['fields'])) {
            $settings = $this->convertFieldsSettings($legacySettings['control_results']['visual']['results']['fields'], $settings);
        }

        // current search
        if (isset($legacySettings['control_results']['visual']['results']['current_search']['enabled'])) {
            $settings['general']['current_search'] = $legacySettings['control_results']['visual']['results']['current_search']['enabled'];
        }
        // items character limit
        if (isset($legacySettings['control_results']['visual']['results']['char_limit'])) {
            $settings['items']['description']['characters'] = $legacySettings['control_results']['visual']['results']['char_limit'];
        }
        // items image
        if (isset($legacySettings['control_results']['visual']['results']['image'])) {
            $imgSettings = $legacySettings['control_results']['visual']['results']['image'];
            $settings['items']['image'] = [
                'enabled' => $imgSettings['show'],
                'width' => $imgSettings['size']['width'],
                'height' => $imgSettings['size']['height'],
                'default_image' => (isset($imgSettings['show_default']) ? $imgSettings['show_default'] : false),
                'position' => 'right',
            ];
        }
        // items icon vlieg
        if (isset($legacySettings['control_results']['visual']['results']['logo_vlieg']['show'])) {
            $settings['items']['icon_vlieg']['enabled'] = $legacySettings['control_results']['visual']['results']['logo_vlieg']['show'];
        }
        // detail map
        if (isset($legacySettings['control_results']['visual']['detail']['map'])) {
            $settings['detail_page']['map'] = $legacySettings['control_results']['visual']['detail']['map']['show'];
        }
        // detail image
        if (isset($legacySettings['control_results']['visual']['detail']['image'])) {
            $imgSettings = $legacySettings['control_results']['visual']['detail']['image'];
            $settings['detail_page']['image'] = [
                'enabled' => $imgSettings['show'],
                'width' => $imgSettings['size']['width'],
                'height' => $imgSettings['size']['height'],
                'default_image' => false,
                'position' => 'right',
            ];
        }
        // detail icon vlieg
        if (isset($legacySettings['control_results']['visual']['detail']['logo_vlieg']['show'])) {
            $settings['detail_page']['icon_vlieg']['enabled'] = $legacySettings['control_results']['visual']['detail']['logo_vlieg']['show'];
        }
        // detail language icons
        if (isset($legacySettings['control_results']['visual']['detail']['taaliconen']['show'])) {
            $settings['detail_page']['language_icons']['enabled'] = $legacySettings['control_results']['visual']['detail']['taaliconen']['show'];
        }
        // detail language switcher
        if (isset($legacySettings['control_results']['visual']['detail']['multilingual']['show'])) {
            $settings['detail_page']['language_switcher'] = $legacySettings['control_results']['visual']['detail']['multilingual']['show'];
        }
        // detail share buttons
        if (isset($legacySettings['control_results']['visual']['detail']['uitid']['share_links'])) {
            $settings['detail_page']['share_buttons'] = $legacySettings['control_results']['visual']['detail']['uitid']['share_links'];
        }

        // parameters
        if (isset($legacySettings['control_results']['parameters']['raw'])) {
            $settings['search_params']['query'] = $legacySettings['control_results']['parameters']['raw'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $type);
    }
}
