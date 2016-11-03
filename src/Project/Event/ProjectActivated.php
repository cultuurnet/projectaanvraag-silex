<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

class ProjectActivated extends ProjectEvent
{
    /**
     * @var string
     */
    private $usedCoupon;

    /**
     * ProjectActivated constructor.
     * @param ProjectInterface $project
     *   Project that was activated.
     * @param $usedCoupon
     *   Coupon that is used to activate the project. (optional)
     */
    public function __construct(ProjectInterface $project, $usedCoupon = null)
    {
        parent::__construct($project);

        $this->usedCoupon = $usedCoupon;
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
