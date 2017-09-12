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
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * WidgetMigration constructor.
     *
     * @param array $settings
     * @param string $name
     * @param string $type
     */
    public function __construct(array $settings, $name, $type)
    {
        $this->settings = $settings;
        $this->name = $name;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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

    protected function extendWithGenericSettings($legacySettings, $settings) {
        // header
        if (isset($legacySettings['control_header']['html'])) {
            $settings['header']['body'] = $legacySettings['control_header']['html'];
        }
        // footer
        if (isset($legacySettings['control_footer']['html'])) {
            $settings['footer']['body'] = $legacySettings['control_footer']['html'];
        }
        return $settings;
    }

}
