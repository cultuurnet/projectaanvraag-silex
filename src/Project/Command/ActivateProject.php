<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

class ActivateProject extends ProjectCommand
{
    /**
     * @var string
     */
    private $couponToUse;

    /**
     * Activate project constructor.
     * @param ProjectInterface $project
     *   Project to activate.
     * @param $couponToUse
     *   Coupon to use for activating the project.
     */
    public function __construct(ProjectInterface $project, $couponToUse = null)
    {
        parent::__construct($project);

        $this->couponToUse = $couponToUse;
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
