<?php

namespace CultuurNet\ProjectAanvraag\Widget\Entities;

use CultuurNet\ProjectAanvraag\DocumentSerializerTrait;
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
class WidgetPageEntity implements WidgetPageInterface, \JsonSerializable
{

    use DocumentSerializerTrait;

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
     * @var array
     *
     * @ODM\Field(type="page_rows")
     */
    protected $rows;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
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

    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toJSON();
    }
}
