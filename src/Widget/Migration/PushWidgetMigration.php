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
        $type = 'tips';

        $settings = [];

        // items amount
        $settings['general']['items'] = (isset($legacySettings['content']['count']) ? $legacySettings['content']['count'] : 3);
        // items image
        if (isset($legacySettings['visual']['image'])) {
            $img_settings = $legacySettings['visual']['image'];
            $settings['items']['image'] = [
                'enabled' => $img_settings['show'] ?? false,
                'width' => $img_settings['size']['width'],
                'height' => $img_settings['size']['height'],
                'default_image' => $img_settings['show_default'] ?? false,
                'position' => 'left',
            ];
        }

        // Add generic fields settings;
        if (isset($legacySettings['visual']['fields'])) {
            $settings = $this->convertFieldsSettings($legacySettings['visual']['fields'], $settings);

            // Add extra setting if there is a read more field added.
            if (isset($settings['items']['read_more'])) {
                // There is no checkbox on the legacy tips form.
                $settings['general']['detail_link']['enabled'] = true;
            }
        }

        // detail url
        if (isset($legacySettings['visual']['detail_url'])) {
            $settings['general']['detail_link']['url'] = $legacySettings['visual']['detail_url']; // ??
        }
        // cdbid
        if (isset($legacySettings['visual']['cdbid_querystring'])) {
            if (!$legacySettings['visual']['cdbid_querystring']) {
                $settings['general']['detail_link']['cbdid'] = 'query_string';
            }
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $type);
    }

}
