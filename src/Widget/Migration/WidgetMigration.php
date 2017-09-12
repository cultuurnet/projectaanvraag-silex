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
     * WidgetMigration constructor.
     *
     * @param $settings
     */
    public function __construct($settings)
    {
        $this->settings = $settings;
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
}
