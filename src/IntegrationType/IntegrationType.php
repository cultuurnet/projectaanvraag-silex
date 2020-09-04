<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

/**
 * Class for integration types
 */
class IntegrationType implements \JsonSerializable
{

    /**
     * The action button that targets widget application.
     */
    const ACTION_BUTTON_WIDGETS = 'widgets';

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $getStartedUrl;

    /**
     * @var array
     */
    protected $extraInfo;

    /**
     * @var string
     */
    protected $groupId;

    /**
     * @var string
     */
    protected $actionButton;

    /**
     * @var string
     */
    protected $sapiVersion;

    /**
     * @var boolean
     */
    protected $selfService;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var boolean
     */
    protected $enableActivation;

    /**
     * @var array
     */
    private $uitIdPermissionGroups;

    /**
     * @var array
     */
    private $uitPasPermissionGroups;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return IntegrationType
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return IntegrationType
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
     * @return IntegrationType
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return IntegrationType
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return IntegrationType
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getGetStartedUrl()
    {
        return $this->getStartedUrl;
    }

    /**
     * @param string $url
     * @return IntegrationType
     */
    public function setGetStartedUrl($url)
    {
        $this->getStartedUrl = $url;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraInfo()
    {
        return $this->extraInfo;
    }

    /**
     * @param array $extraInfo
     * @return IntegrationType
     */
    public function setExtraInfo($extraInfo)
    {
        $this->extraInfo = $extraInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     * @return IntegrationType
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getActionButton()
    {
        return $this->actionButton;
    }

    /**
     * @param string $actionButton
     * @return IntegrationType
     */
    public function setActionButton($actionButton)
    {
        $this->actionButton = $actionButton;
        return $this;
    }

    /**
     * @return string
     */
    public function getSapiVersion()
    {
        return $this->sapiVersion;
    }

    /**
     * @param string $actionButton
     * @return IntegrationType
     */
    public function setSapiVersion($version)
    {
        $this->sapiVersion = $version;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSelfService()
    {
        return $this->selfService;
    }

    /**
     * @param boolean $selfService
     * @return IntegrationType
     */
    public function setSelfService($selfService)
    {
        $this->selfService = $selfService;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableActivation()
    {
        return $this->enableActivation;
    }

    /**
     * @param boolean $enableActivation
     * @return IntegrationType
     */
    public function setEnableActivation($enableActivation)
    {
        $this->enableActivation = $enableActivation;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return IntegrationType
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getUitIdPermissionGroups(): array
    {
        return $this->uitIdPermissionGroups;
    }

    public function setUitIdPermissionGroups($uitIdPermissionGroups): void
    {
        $this->uitIdPermissionGroups = $uitIdPermissionGroups;
    }

    public function getUitPasPermissionGroups(): array
    {
        return $this->uitPasPermissionGroups;
    }

    public function setUitPasPermissionGroups($uitPasPermissionGroups): void
    {
        $this->uitPasPermissionGroups = $uitPasPermissionGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $json = [];

        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $json[$key] = $value;
            }
        }

        return $json;
    }
}
