<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedTrait;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\RabbitMQ\DelayableMessageInterface;
use JMS\Serializer\Annotation\Type;

abstract class ProjectEvent implements AsynchronousMessageInterface, MessageAttemptedInterface, DelayableMessageInterface
{
    use MessageAttemptedTrait;

    /**
     * @var ProjectInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\Project")
     */
    private $project;

    /**
     * @var int
     * @Type("integer")
     */
    private $delay;

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
     * @return ProjectEvent
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }

    /**
     * Set the delay in milliseconds
     * @param int $delay
     * @return ProjectEvent
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDelay()
    {
        return $this->delay;
    }
}
