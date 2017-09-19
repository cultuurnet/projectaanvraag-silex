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
        if (isset($legacySettings['control_results']['visual']['results']['char_limit'])){
            $settings['items']['description']['characters'] = $legacySettings['control_results']['visual']['results']['char_limit'];
        }
        // items image
        if (isset($legacySettings['control_results']['visual']['results']['image'])) {
            $img_settings = $legacySettings['control_results']['visual']['results']['image'];
            $settings['items']['image'] = [
                'enabled' => $img_settings['show'],
                'width' => $img_settings['size']['width'],
                'height' => $img_settings['size']['height'],
                'default_image' => (isset($img_settings['show_default']) ? $img_settings['show_default'] : false),
                'position' => 'left',
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
            $img_settings = $legacySettings['control_results']['visual']['detail']['image'];
            $settings['detail_page']['image'] = [
                'enabled' => $img_settings['show'],
                'width' => $img_settings['size']['width'],
                'height' => $img_settings['size']['height'],
                'default_image' => false,
                'position' => 'left',
            ];
        }
        // detail icon vlieg
        if (isset($legacySettings['control_results']['visual']['detail']['logo_vlieg']['show'])) {
            $settings['detail_page']['language_switcher'] = $legacySettings['control_results']['visual']['detail']['logo_vlieg']['show'];
        }
        // detail language icons
        if (isset($legacySettings['control_results']['visual']['detail']['taaliconen']['show'])) {
            $settings['detail_page']['language_icons'] = $legacySettings['control_results']['visual']['detail']['taaliconen']['show'];
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
