<?php

namespace CultuurNet\ProjectAanvraag\Widget\Entities;

use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        /**
         * Serialize all rows.
         */
        $rows = [];
        /** @var LayoutInterface $row */
        foreach ($this->rows as $row) {
            $rows[] = $row->jsonSerialize();
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'rows' => $rows,
        ];
    }
}
