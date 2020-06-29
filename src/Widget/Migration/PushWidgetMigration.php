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
            $imgSettings = $legacySettings['visual']['image'];
            $settings['items']['image'] = [
                'enabled' => $imgSettings['show'] ?? false,
                'width' => $imgSettings['size']['width'],
                'height' => $imgSettings['size']['height'],
                'position' => 'left',
            ];
        }

        if (isset($legacySettings['items']['image'])) {
          $imgSettings = $legacySettings['items']['image'];
          $settings['items']['image'] = $imgSettings;
          $settings['items']['image']['default_image'] = [
            'enabled' => $imgSettings['enabled'] ?? false,
            'type' => 'uit',
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

        // parameters
        if (isset($legacySettings['content']['parameters']['raw'])) {
            $settings['search_params']['query'] = $legacySettings['content']['parameters']['raw'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $type);
    }
}
