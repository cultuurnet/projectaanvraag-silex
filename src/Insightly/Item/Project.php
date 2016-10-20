<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Project extends Entity
{
    const STATUS_ABANDONED = 'ABANDONED';
    const STATUS_CANCELLED = 'CANCELLED';
    const STATUS_COMPLETED = 'COMPLETED';
    const STATUS_DEFERRED = 'DEFEREDD';
    const STATUS_IN_PROGRESS = 'IN PROGRESS';
    const STATUS_NOT_STARTED = 'NOT STARTED';

    const VISIBILITY_EVERYONE = 'EVERYONE';
    const VISIBILITY_OWNER = 'OWNER';
    const VISIBILITY_TEAM = 'TEAM';
    const VISIBILITY_INDIVIDUALS = 'INDIVIDUALS';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $details;

    /**
     * @var int
     */
    protected $opportunityId;

    /**
     * @var \DateTime
     */
    protected $startedDate;

    /**
     * @var \DateTime
     */
    protected $completedDate;

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var int
     */
    protected $responsibleUserId;

    /**
     * @var int
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
     * @var int
     */
    protected $categoryId;

    /**
     * @var int
     */
    protected $pipelineId;

    /**
     * @var int
     */
    protected $stageId;

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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = strtoupper($status);
        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return Project
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return int
     */
    public function getOpportunityId()
    {
        return $this->opportunityId;
    }

    /**
     * @param int $opportunityId
     * @return Project
     */
    public function setOpportunityId($opportunityId)
    {
        $this->opportunityId = $opportunityId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartedDate()
    {
        return $this->startedDate;
    }

    /**
     * @param \DateTime $startedDate
     * @return Project
     */
    public function setStartedDate($startedDate)
    {
        $this->startedDate = $startedDate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCompletedDate()
    {
        return $this->completedDate;
    }

    /**
     * @param \DateTime $completedDate
     * @return Project
     */
    public function setCompletedDate($completedDate)
    {
        $this->completedDate = $completedDate;
        return $this;
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
     * @return Project
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponsibleUserId()
    {
        return $this->responsibleUserId;
    }

    /**
     * @param int $responsibleUserId
     * @return Project
     */
    public function setResponsibleUserId($responsibleUserId)
    {
        $this->responsibleUserId = $responsibleUserId;
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
     * @return Project
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
     * @return Project
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
     * @return Project
     */
    public function setDateUpdatedUTC($dateUpdatedUTC)
    {
        $this->dateUpdatedUTC = $dateUpdatedUTC;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     * @return Project
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPipelineId()
    {
        return $this->pipelineId;
    }

    /**
     * @param int $pipelineId
     * @return Project
     */
    public function setPipelineId($pipelineId)
    {
        $this->pipelineId = $pipelineId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStageId()
    {
        return $this->stageId;
    }

    /**
     * @param int $stageId
     * @return Project
     */
    public function setStageId($stageId)
    {
        $this->stageId = $stageId;
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
     * @return Project
     */
    public function setVisibleTo($visibleTo)
    {
        $this->visibleTo = strtoupper($visibleTo);
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
     * @return Project
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
     * @return Project
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
     * @return Project
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
     * @return Project
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
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
     * @return Project
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
     * @return Project
     */
    public function setCanDelete($canDelete)
    {
        $this->canDelete = $canDelete;
        return $this;
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
}
