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
     * Get the identifier of the widget type.
     */
    public function getId()
    {
        return $this->id;
    }
}
