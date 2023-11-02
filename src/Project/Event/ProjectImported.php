<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;

class ProjectImported extends AbstractProjectEvent
{
    /**
     * @var string
     * @Type("string")
     */
    private $usedCoupon;

    /**
     * @var UserInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\User")
     */
    private $user;

    /**
     * ProjectCreated constructor.
     * @param ProjectInterface $project
     *   Project that was created.
     * @param UserInterface $user
     * @param $usedCoupon
     *   Coupon that is used to create the project. (optional)
     */
    public function __construct(ProjectInterface $project, UserInterface $user, $usedCoupon = null)
    {
        parent::__construct($project);

        $this->usedCoupon = $usedCoupon;
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     * @return ProjectImported
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsedCoupon()
    {
        return $this->usedCoupon;
    }
}
