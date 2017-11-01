<?php

namespace CultuurNet\ProjectAanvraag\Widget\Migration;

/**
 * Class WidgetMigration
 * @package CultuurNet\ProjectAanvraag\Widget\Migration
 */
abstract class WidgetMigration
{
    /**
     * @var array
     */
    private $settings;

    /**
     * @var string
     */
    private $type;

    /**
     * WidgetMigration constructor.
     *
     * @param array $settings
     * @param string $type
     */
    public function __construct(array $settings, $type)
    {
        $this->settings = $settings;
        $this->type = $type;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Add generic settings that are common across legacy controls.
     *
     * @param $legacySettings
     * @param $settings
     * @return array
     */
    protected function extendWithGenericSettings($legacySettings, $settings)
    {
        if (isset($legacySettings['control_header']['html']) && $legacySettings['control_header']['html'] != '') {
            $settings['header']['body'] = $legacySettings['control_header']['html'];
        }
        if (isset($legacySettings['control_footer']['html']) && $legacySettings['control_footer']['html'] != '') {
            $settings['footer']['body'] = $legacySettings['control_footer']['html'];
        }
        return $settings;
    }

    /**
     * Convert legacy "fields" settings to generic settings.
     *
     * @param $legacyFields
     * @param $settings
     * @return array
     */
    protected function convertFieldsSettings($legacyFields, $settings)
    {
        foreach ($legacyFields as $key => $value) {
            $label = '';
            // Change variables when values are subarrays.
            if (is_array($value)) {
                $label = $value['label'];
                $key = $value['id'];
                $value = $value['id'];
            }

            switch ($key) {
                case 'location':
                    // where
                    $settings['items']['where']['enabled'] = $value;
                    $settings['items']['where']['label'] = $label;
                    break;
                case 'calendarsummary':
                    // when
                    $settings['items']['when']['enabled'] = $value;
                    $settings['items']['when']['label'] = $label;
                    break;
                case 'agefrom':
                    // age
                    $settings['items']['age']['enabled'] = $value;
                    $settings['items']['age']['label'] = $label;
                    break;
                case 'taaliconen':
                    // language icons
                    $settings['items']['language_icons']['enabled'] = $value;
                    $settings['items']['language_icons']['label'] = $label;
                    break;
                case 'readmore':
                    $settings['items']['read_more']['enabled'] = $value;
                    $settings['items']['read_more']['label'] = $label;
                    break;
                case 'shortdescription':
                    // description (no label)
                    $settings['items']['description']['enabled'] = $value;
                    break;
                case 'labels':
                    $settings['items']['labels']['enabled'] = $value;
                    $settings['items']['labels']['label'] = $label;
                    break;
            }
        }
        return $settings;
    }
}
