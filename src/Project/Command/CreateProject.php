<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class CreateProject
{
    /**
     * @var string
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return CreateProject
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
