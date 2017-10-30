<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Project extends PrimaryEntityBase
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
     * @var int
     */
    protected $responsibleUserId;

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
     * Serializes a Project to an Insightly accepted array
     * @return array
     */
    public function toInsightly()
    {
        $data = parent::toInsightly();

        $data += [
            'PROJECT_ID' => $this->getId(),
            'PROJECT_NAME' => $this->stripSlashes($this->getName()),
            'STATUS' => $this->getStatus(),
            'PROJECT_DETAILS' => $this->stripSlashes($this->getDetails()),
            'OPPORTUNITY_ID' => $this->getOpportunityId(),
            'STARTED_DATE' => !empty($this->getStartedDate()) ? $this->getStartedDate()->format('Y-m-d H:i:s') : null,
            'COMPLETED_DATE' => !empty($this->getCompletedDate()) ? $this->getCompletedDate()->format('Y-m-d H:i:s') : null,
            'RESPONSIBLE_USER_ID' => $this->getResponsibleUserId(),
            'CATEGORY_ID' => $this->getCategoryId(),
            'PIPELINE_ID' => $this->getPipelineId(),
            'STAGE_ID' => $this->getStageId(),
        ];

        return array_filter($data);
    }

    /**
     * @param string $string
     * @return string
     */
    private function stripSlashes($string)
    {
        return str_replace('/', '-', $string);
    }
}
