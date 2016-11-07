<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\InsightlySerializableInterface;

/**
 * Base class for primary entities. (projects, organisations, ...)
 */
abstract class PrimaryEntityBase extends Entity implements \JsonSerializable, InsightlySerializableInterface
{

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var integer
     */
    protected $ownerUserId;

    /**
     * @var \DateTime
     */
    protected $dateCreatedUTC;

    /**
     * @var \DateTime
     */
    protected $dateUpdatedUTC;

    /**
     * @var string
     */
    protected $visibleTo;

    /**
     * @var int
     */
    protected $visibleTeamId;

    /**
     * @var array
     */
    protected $visibleUserIds = [];

    /**
     * @var EntityList
     */
    protected $tags;

    /**
     * @var EntityList
     */
    protected $links;

    /**
     * @var bool
     */
    protected $canEdit;

    /**
     * @var bool
     */
    protected $canDelete;

    /**
     * @var array
     */
    protected $customFields = [];

    public function __construct()
    {
        $this->links = new EntityList();
        $this->tags = new EntityList();
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     * @return PrimaryEntityBase
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerUserId()
    {
        return $this->ownerUserId;
    }

    /**
     * @param int $ownerUserId
     * @return PrimaryEntityBase
     */
    public function setOwnerUserId($ownerUserId)
    {
        $this->ownerUserId = $ownerUserId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreatedUTC()
    {
        return $this->dateCreatedUTC;
    }

    /**
     * @param \DateTime $dateCreatedUTC
     * @return PrimaryEntityBase
     */
    public function setDateCreatedUTC($dateCreatedUTC)
    {
        $this->dateCreatedUTC = $dateCreatedUTC;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdatedUTC()
    {
        return $this->dateUpdatedUTC;
    }

    /**
     * @param \DateTime $dateUpdatedUTC
     * @return PrimaryEntityBase
     */
    public function setDateUpdatedUTC($dateUpdatedUTC)
    {
        $this->dateUpdatedUTC = $dateUpdatedUTC;
        return $this;
    }

    /**
     * @return string
     */
    public function getVisibleTo()
    {
        return $this->visibleTo;
    }

    /**
     * @param string $visibleTo
     * @return PrimaryEntityBase
     */
    public function setVisibleTo($visibleTo)
    {
        $this->visibleTo = $visibleTo;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibleTeamId()
    {
        return $this->visibleTeamId;
    }

    /**
     * @param int $visibleTeamId
     * @return PrimaryEntityBase
     */
    public function setVisibleTeamId($visibleTeamId)
    {
        $this->visibleTeamId = $visibleTeamId;
        return $this;
    }

    /**
     * @return array
     */
    public function getVisibleUserIds()
    {
        return $this->visibleUserIds;
    }

    /**
     * @param array $visibleUserIds
     * @return PrimaryEntityBase
     */
    public function setVisibleUserIds($visibleUserIds)
    {
        $this->visibleUserIds = $visibleUserIds;
        return $this;
    }

    /**
     * @return EntityList
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param EntityList $tags
     * @return PrimaryEntityBase
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return EntityList
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param EntityList $links
     * @return PrimaryEntityBase
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Add a link.
     */
    public function addLink(Link $link)
    {
        $this->links[] = $link;
    }

    /**
     * Remove a link.
     * @var key
     *   Index to unset.
     */
    public function removeLink($key)
    {
        unset($this->links[$key]);
    }

    /**
     * @return boolean
     */
    public function canEdit()
    {
        return $this->canEdit;
    }

    /**
     * @param boolean $canEdit
     * @return PrimaryEntityBase
     */
    public function setCanEdit($canEdit)
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    /**
     * @return boolean
     */
    public function canDelete()
    {
        return $this->canDelete;
    }

    /**
     * @param boolean $canDelete
     * @return PrimaryEntityBase
     */
    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    /**
     * @return array
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * @param array $customFields
     * @return PrimaryEntityBase
     */
    public function setCustomFields($customFields)
    {
        $this->customFields = $customFields;
        return $this;
    }

    /**
     * Add a custom field.
     * @param $key
     *   Key to add
     * @param $value
     *   Value for the custom field.
     */
    public function addCustomField($key, $value)
    {
        $this->customFields[$key] = $value;
    }

    /**
     * Delete a custom field.
     * @param $key
     *   Key to remove
     */
    public function deleteCustomField($key)
    {
        unset($this->customFields[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();

        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function toInsightly()
    {
        $customFields = [];
        foreach ($this->customFields as $key => $value) {
            $customFields[] = [
                'CUSTOM_FIELD_ID' => $key,
                'FIELD_VALUE' => $value,
            ];
        }

        $links = [];
        if (!empty($this->links)) {
            /** @var Link $link */
            foreach ($this->links as $link) {
                $links[] = $link->toInsightly();
            }
        }

        return [
            'IMAGE_URL' => $this->getImageUrl(),
            'OWNER_USER_ID' => $this->getOwnerUserId(),
            'DATE_CREATED_UTC' => !empty($this->getDateCreatedUTC()) ? $this->getDateCreatedUTC()->format('Y-m-d H:i:s') : null,
            'DATE_UPDATED_UTC' => !empty($this->getDateUpdatedUTC()) ? $this->getDateUpdatedUTC()->format('Y-m-d H:i:s') : null,
            'VISIBLE_TO' => $this->getVisibleTo(),
            'VISIBLE_TEAM_ID' => $this->getVisibleTeamId(),
            'VISIBLE_USER_IDS' => $this->getVisibleUserIds(),
            'CUSTOMFIELDS' => $customFields,
            'LINKS' => $links,
            'CAN_EDIT' => $this->canEdit(),
            'CAN_DELETE' => $this->canDelete(),
        ];
    }
}
