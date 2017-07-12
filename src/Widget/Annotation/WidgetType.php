<?php

namespace CultuurNet\ProjectAanvraag\Widget\Annotation;

/**
 * Defines a widget type annotation object.
 *
 * @Annotation
 */
class WidgetType
{

    /**
     * The widget type id.
     *
     * @var string
     */
    public $id;

    /**
     * The widget default settings.
     *
     * @var array
     */
    public $defaultSettings;

    /**
     * Array representation of the allowed setting structure.
     *
     * @var array
     */
    public $allowedSettings;

    /**
     * Get the identifier of the widget type.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the default settings for the widget type.
     */
    public function getDefaultSettings()
    {
        return $this->defaultSettings;
    }

    /**
     * Get the allowed settings for the widget type.
     */
    public function getAllowedSettings()
    {
        return $this->allowedSettings;
    }
}
