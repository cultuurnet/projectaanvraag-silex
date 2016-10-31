<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use CultuurNet\ProjectAanvraag\Entity\Project;
use JMS\Serializer\Annotation\Type;

class ProjectActivated implements AsynchronousMessageInterface
{
    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $usedCoupon;

    /**
     * ProjectActivated constructor.
     * @param Project $project
     *   Project that was activated.
     * @param $usedCoupon
     *   Coupon that is used to activate the project. (optional)
     */
    public function __construct(Project $project, $usedCoupon = null)
    {
        $this->project = $project;
        $this->usedCoupon = $usedCoupon;
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
     * @return ProjectActivated
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsedCoupon()
    {
        return $this->usedCoupon;
    }

    /**
     * @param string $usedCoupon
     * @return ProjectActivated
     */
    public function setUsedCoupon($usedCoupon)
    {
        $this->usedCoupon = $usedCoupon;
        return $this;
    }
}
