<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedTrait;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

abstract class ProjectEvent implements AsynchronousMessageInterface, MessageAttemptedInterface
{
    use MessageAttemptedTrait;

    /**
     * @var ProjectInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\Project")
     */
    private $project;

    /**
     * ProjectDeleted constructor.
     * @param ProjectInterface $project
     */
    public function __construct($project)
    {
        $this->project = $project;
    }

    /**
     * @return ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param ProjectInterface $project
     * @return ProjectEvent
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }
}
