<?php

namespace CultuurNet\ProjectAanvraag\Widget\Annotation;

/**
 * Defines a layout annotation object.
 *
 * @Annotation
 */
class Layout
{

    /**
     * The layout id.
     *
     * @var string
     */
    public $id;

    /**
     * Get the identifier of the layout.
     */
    public function getId()
    {
        return $this->id;
    }
}
