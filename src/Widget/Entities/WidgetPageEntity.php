<?php

namespace CultuurNet\ProjectAanvraag\Widget\Entities;

use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Provides a widget page entity.
 *
 * @ODM\Document(
 *     collection="WidgetPage",
 *     requireIndexes=false
 * )
 */
class WidgetPageEntity implements WidgetPageInterface
{

    /**
     * @var string
     *
     * @ODM\Id
     */
    protected $id;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $title;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}