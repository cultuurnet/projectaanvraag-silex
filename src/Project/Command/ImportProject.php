<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class ImportProject
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
    protected $platformUuid;

    /**
     * @var string
     */
    private $testApiKeySapi3;

    /**
     * @var string
     */
    private $liveApiKeySapi3;

    /**
     * ImportProject constructor.
     * @param $name
     * @param $description
     * @param int $integrationType
     * @param string $platformUuid
     * @param string $testApiKeySapi3
     * @param string $liveApiKeySapi3
     */
    public function __construct($name, $description, $integrationType, $platformUuid, $testApiKeySapi3, $liveApiKeySapi3)
    {
        $this->name = $name;
        $this->description = $description;
        $this->integrationType = $integrationType;
        $this->platformUuid = $platformUuid;
        $this->testApiKeySapi3 = $testApiKeySapi3;
        $this->liveApiKeySapi3 = $liveApiKeySapi3;
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
     * @return ImportProject
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
     * @return ImportProject
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
     * @return ImportProject
     */
    public function setIntegrationType($integrationType)
    {
        $this->integrationType = $integrationType;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatformUuid()
    {
        return $this->platformUuid;
    }

    /**
     * @param string $platformUuid
     * @return ImportProject
     */
    public function setPlatformUuid($platformUuid)
    {
        $this->platformUuid = $platformUuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestApiKeySapi3()
    {
        return $this->testApiKeySapi3;
    }

    /**
     * @param string $testApiKeySapi3
     * @return ImportProject
     */
    public function setTestApiKeySapi3($testApiKeySapi3)
    {
        $this->testApiKeySapi3 = $testApiKeySapi3;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiveApiKeySapi3()
    {
        return $this->liveApiKeySapi3;
    }

    /**
     * @param string $liveApiKeySapi3
     * @return ImportProject
     */
    public function setLiveApiKeySapi3($liveApiKeySapi3)
    {
        $this->liveApiKeySapi3 = $liveApiKeySapi3;
        return $this;
    }
}
