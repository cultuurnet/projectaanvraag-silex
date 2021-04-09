<?php

namespace CultuurNet\ProjectAanvraag\Widget\Entities;

use CultuurNet\ProjectAanvraag\Widget\LayoutInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
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
     * @ODM\Field(type="integer")
     */
    protected $version;

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
     * @ODM\Field(type="string", name="project_id")
     */
    protected $projectId;

    /**
     * @var string
     *
     * @ODM\Field(type="timestamp")
     */
    protected $created;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="created_by")
     */
    protected $createdBy;

    /**
     * @var string
     *
     * @ODM\Field(type="timestamp", name="last_updated")
     */
    protected $lastUpdated;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="last_updated_by")
     */
    protected $lastUpdatedBy;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="css")
     */
    protected $css;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="selectedTheme")
     */
    protected $selectedTheme;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="mobile")
     */
    protected $mobile;

    /**
     * @var boolean
     *
     * @ODM\Field(type="boolean", name="jquery")
     */
    protected $jquery;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="language")
     */
    protected $language;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return WidgetPageEntity
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

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

    public function isDraft()
    {
        return $this->draft;
    }

    public function setAsDraft()
    {
        $this->draft = true;
    }

    public function publish()
    {
        $this->draft = false;
    }

    public function getProjectId()
    {
        return $this->projectId;
    }

    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    public function getLastUpdatedBy()
    {
        return $this->lastUpdatedBy;
    }

    public function setLastUpdatedBy($userID)
    {
        $this->lastUpdatedBy = $userID;
    }

    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function setCreatedBy($userID)
    {
        $this->createdBy = $userID;
    }

    public function getCreated()
    {
        return (string) $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastUpdated()
    {
        return (string) $this->lastUpdated;
    }

    public function setLastUpdated($updated)
    {
        $this->lastUpdated = $updated;
        return $this;
    }

    public function getCss()
    {
        return $this->css;
    }

    public function setCss($css)
    {
        $this->css = $css;
    }

    public function getSelectedTheme() : ?string
    {
        return $this->selectedTheme;
    }

    public function setSelectedTheme(?string $selectedTheme)
    {
        $this->selectedTheme = $selectedTheme;
    }

    public function getMobile()
    {
        return $this->mobile;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    public function getJquery()
    {
        return $this->jquery;
    }

    public function setJquery($jquery)
    {
        $this->jquery = $jquery;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getWidget($widgetId)
    {
        /** @var LayoutInterface $row */
        foreach ($this->rows as $row) {
            if ($row->hasWidget($widgetId)) {
                return $row->getWidget($widgetId);
            }
        }
    }

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
            'version' => $this->version,
            'title' => $this->title,
            'rows' => $rows,
            'draft' => $this->draft,
            'project_id' => $this->projectId,
            'created_by' => $this->createdBy,
            'last_updated_by' => $this->lastUpdatedBy,
            'created' => (string) $this->created,
            'last_updated' => (string) $this->lastUpdated,
            'css' => (string) $this->css,
            'selectedTheme' => (string) $this->selectedTheme,
            'mobile' => (boolean) $this->mobile,
            'jquery' => (boolean) $this->jquery,
            'language' => (string) ($this->language) ?: 'nl' ,
        ];
    }
}
