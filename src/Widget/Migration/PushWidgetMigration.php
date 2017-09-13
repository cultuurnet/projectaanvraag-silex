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
        $name = 'tips-1';
        $type = 'tips';

        $settings = [];

        // items amount
        if (isset($legacySettings['content']['count'])) {
            $settings['general']['items'] = $legacySettings['content']['count'];
        }
        // items image
        if (isset($legacySettings['visual']['image'])) {
            $img_settings = $legacySettings['visual']['image'];
            $settings['items']['image'] = [
                'enabled' => $img_settings['show'],
                'width' => $img_settings['size']['width'],
                'height' => $img_settings['size']['height'],
                'default_image' => $img_settings['show_default'] ?? false,
                'position' => 'left',
            ];
        }
        // where
        if (isset($legacySettings['visual']['fields']['location'])) {
            $settings['items']['where']['enabled'] = $legacySettings['visual']['fields']['location'];
        }
        // age
        if (isset($legacySettings['visual']['fields']['agefrom'])) {
            $settings['items']['age']['enabled'] = $legacySettings['visual']['fields']['agefrom'];
        }
        // read more
        if (isset($legacySettings['visual']['fields']['readmore'])) {
            $settings['items']['read_more']['enabled'] = $legacySettings['visual']['fields']['readmore'];
        }

        parent::__construct($this->extendWithGenericSettings($legacySettings, $settings), $name, $type);
    }

}
