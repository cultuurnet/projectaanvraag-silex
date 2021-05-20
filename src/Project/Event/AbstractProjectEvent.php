<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

abstract class AbstractProjectEvent extends AbstractRetryableMessage
{
    /**
     * @var ProjectInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\Project")
     */
    private $project;

    /**
     * ProjectDeleted constructor.
     * @param ProjectInterface $project
     * @param int $delay
     */
    public function __construct($project, $delay = 0)
    {
        $this->project = $project;
        $this->delay = $delay;
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
     * @return AbstractProjectEvent
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }
}
