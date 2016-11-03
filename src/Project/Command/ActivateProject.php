<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Entity\Project;

class ActivateProject
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $couponToUse;

    /**
     * Activate project constructor.
     * @param Project $project
     *   Project to activate.
     * @param $couponToUse
     *   Coupon to use for activating the project.
     */
    public function __construct(Project $project, $couponToUse = null)
    {
        $this->project = $project;
        $this->couponToUse = $couponToUse;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     * @return ActivateProject
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return string
     */
    public function getCouponToUse()
    {
        return $this->couponToUse;
    }

    /**
     * @param string $couponToUse
     * @return ActivateProject
     */
    public function setCouponToUse($couponToUse)
    {
        $this->couponToUse = $couponToUse;
        return $this;
    }
}
