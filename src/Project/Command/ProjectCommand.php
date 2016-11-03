<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use JMS\Serializer\Annotation\Type;

abstract class ProjectCommand
{
    /**
     * @var ProjectInterface
     * @Type("CultuurNet\ProjectAanvraag\Entity\Project")
     */
    private $project;

    /**
     * DeleteProject constructor.
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
     * @return ProjectCommand
     */
    public function setProject($project)
    {
        $this->project = $project;
        return $this;
    }
}
