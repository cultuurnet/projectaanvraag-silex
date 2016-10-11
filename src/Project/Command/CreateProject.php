<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class CreateProject
{

    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

}