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
     * The internal mongodb id.
     * @var string
     *
     * @ODM\Id(strategy="UUID", type="string")
     */
    protected $internalId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
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
     * @var boolean
     *
     * @ODM\Field(type="boolean")
     */
    protected $draft;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $projectId;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $createdByUser;

    /**
     * @var string
     *
     * @ODM\Field(type="string")
     */
    protected $lastUpdatedByUser;

    /**
     * @var
     */
    protected $css;

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
    public function isDraft()
    {
        return $this->draft;
    }

    /**
     * {@inheritdoc}
     */
    public function setAsDraft()
    {
        $this->draft = true;
    }

    /**
     * @return string
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param string $projectId
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return string
     */
    public function getLastUpdatedByUser()
    {
        return $this->lastUpdatedByUser;
    }

    /**
     * @param string $lastUpdatedByUser
     */
    public function setLastUpdatedByUser($userID)
    {
        $this->lastUpdatedByUser = $userID;
    }

    /**
     * @return mixed
     */
    public function getCreatedByUser()
    {
        return $this->createdByUser;
    }

    /**
     * @param mixed $generatedByUser
     */
    public function setCreatedByUser($userID)
    {
        $this->createdByUser = $userID;
    }

    /**
     * @return mixed
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * @param mixed $css
     */
    public function setCss($css)
    {
        $this->css = $css;
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
            'draft' => $this->draft,
            'project_id' => $this->projectId,
            'createdByUser' => $this->createdByUser,
            'lastUpdatedByuser' => $this->lastUpdatedByUser,
        ];
    }
}
