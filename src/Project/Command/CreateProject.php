<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class CreateProject
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $integrationType;

    /**
     * CreateProject constructor.
     * @param $name
     * @param $description
     * @param int $integrationType
     */
    public function __construct($name, $description, $integrationType)
    {
        $this->name = $name;
        $this->description = $description;
        $this->integrationType = $integrationType;
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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return CreateProject
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return int
     */
    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    /**
     * @param int $integrationType
     * @return CreateProject
     */
    public function setIntegrationType($integrationType)
    {
        $this->integrationType = $integrationType;
        return $this;
    }
}
