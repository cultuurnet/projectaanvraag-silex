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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getIntegrationType()
    {
        return $this->integrationType;
    }

    /**
     * @return string
     */
    public function getPlatformUuid()
    {
        return $this->platformUuid;
    }

    /**
     * @return string
     */
    public function getTestApiKeySapi3()
    {
        return $this->testApiKeySapi3;
    }

    /**
     * @return string
     */
    public function getLiveApiKeySapi3()
    {
        return $this->liveApiKeySapi3;
    }
}
