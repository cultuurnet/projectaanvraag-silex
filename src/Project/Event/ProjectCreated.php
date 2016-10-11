<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessage;
use JMS\Serializer\Annotation\Type;

class ProjectCreated implements AsynchronousMessage
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