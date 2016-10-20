<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class Pipeline extends Entity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $forOpportunities;

    /**
     * @var bool
     */
    protected $forProjects;

    /**
     * @var int
     */
    protected $ownerUserId;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Pipeline
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return Pipeline
     */
    public function setOwnerUserId($ownerUserId)
    {
        $this->ownerUserId = $ownerUserId;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isForOpportunities()
    {
        return $this->forOpportunities;
    }

    /**
     * @param boolean $forOpportunities
     * @return Pipeline
     */
    public function setForOpportunities($forOpportunities)
    {
        $this->forOpportunities = $forOpportunities;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isForProjects()
    {
        return $this->forProjects;
    }

    /**
     * @param boolean $forProjects
     * @return Pipeline
     */
    public function setForProjects($forProjects)
    {
        $this->forProjects = $forProjects;
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
