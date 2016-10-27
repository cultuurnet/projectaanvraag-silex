<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use JMS\Serializer\Annotation\Type;

class ProjectCreated implements AsynchronousMessageInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * ProjectCreated constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ProjectCreated
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
