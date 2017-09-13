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
                'default_image' => $img_settings['show_default'],
                'position' => 'left',
            ];
        }
        // items icon vlieg
        if (isset($legacySettings['control_results']['visual']['results']['logo_vlieg']['show'])) {
            $settings['items']['icon_vlieg'] = $legacySettings['control_results']['visual']['results']['logo_vlieg']['show'];
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
            $settings['detail_page']['icon_vlieg'] = $legacySettings['control_results']['visual']['detail']['logo_vlieg']['show'];
        }
        // detail language icons
        if (isset($legacySettings['control_results']['visual']['detail']['taaliconen']['show'])) {
            $settings['detail_page']['language_icons'] = $legacySettings['control_results']['visual']['detail']['taaliconen']['show'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $name, $type);
    }

}
