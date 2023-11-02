<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use JMS\Serializer\Annotation\Type;

class ProjectImported extends AbstractProjectEvent
{
    /**
     * @var UserInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\User")
     */
    private $user;

    public function __construct(ProjectInterface $project, UserInterface $user)
    {
        parent::__construct($project);

        $this->user = $user;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
