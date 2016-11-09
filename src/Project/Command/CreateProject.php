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
     * @var string
     */
    private $couponToUse;

    /**
     * CreateProject constructor.
     * @param $name
     * @param $description
     * @param int $integrationType
     * @param string|null $couponToUse
     */
    public function __construct($name, $description, $integrationType, $couponToUse = null)
    {
        $this->name = $name;
        $this->description = $description;
        $this->integrationType = $integrationType;
        $this->couponToUse = $couponToUse;
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

    /**
     * @return mixed
     */
    public function getCouponToUse()
    {
        return $this->couponToUse;
    }

    /**
     * @param mixed $couponToUse
     * @return CreateProject
     */
    public function setCouponToUse($couponToUse)
    {
        $this->couponToUse = $couponToUse;
        return $this;
    }
}
