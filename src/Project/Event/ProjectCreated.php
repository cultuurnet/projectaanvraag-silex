<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use JMS\Serializer\Annotation\Type;

class ProjectCreated implements AsynchronousMessageInterface
{

    /**
     * @Type("integer")
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
