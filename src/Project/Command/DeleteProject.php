<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class DeleteProject
{
    /**
     * @var string
     */
    private $id;

    /**
     * DeleteProject constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return DeleteProject
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
