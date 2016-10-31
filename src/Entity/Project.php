<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="project",
 *     indexes={
 *         @ORM\Index(name="uid", columns={"uid"}),
 *         @ORM\Index(name="test_consumer_key", columns={"test_consumer_key"}),
 *         @ORM\Index(name="live_consumer_key", columns={"live_consumer_key"}),
 *     }
 * )
 */
class Project implements EntityInterface, \JsonSerializable
{
    const PROJECT_STATUS_APPLICATION_SENT = 'application_sent';
    const PROJECT_STATUS_ACTIVATE = 'active';

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="uid", type="string", length=255, nullable=true)
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(name="test_consumer_key", type="string", length=255, nullable=true)
     * @var string
     */
    protected $testConsumerKey;

    /**
     * @ORM\Column(name="live_consumer_key", type="string", length=255, nullable=true)
     * @var string
     */
    protected $liveConsumerKey;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $groupId;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $updated;

    /** @var string */
    protected $testConsumerSecret;

    /** @var string */
    protected $liveConsumerSecret;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $logo;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var IntegrationType
     */
    protected $group;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Project
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
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestConsumerKey()
    {
        return $this->testConsumerKey;
    }

    /**
     * @param string $testConsumerKey
     * @return Project
     */
    public function setTestConsumerKey($testConsumerKey)
    {
        $this->testConsumerKey = $testConsumerKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiveConsumerKey()
    {
        return $this->liveConsumerKey;
    }

    /**
     * @param string $liveConsumerKey
     * @return Project
     */
    public function setLiveConsumerKey($liveConsumerKey)
    {
        $this->liveConsumerKey = $liveConsumerKey;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     * @return Project
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Project
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     * @return Project
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     * @return Project
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     * @return Project
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * @return string
     */
    public function getTestConsumerSecret()
    {
        return $this->testConsumerSecret;
    }

    /**
     * @param string $testConsumerSecret
     * @return Project
     */
    public function setTestConsumerSecret($testConsumerSecret)
    {
        $this->testConsumerSecret = $testConsumerSecret;
        return $this;
    }

    /**
     * @return string
     */
    public function getLiveConsumerSecret()
    {
        return $this->liveConsumerSecret;
    }

    /**
     * @param string $liveConsumerSecret
     * @return Project
     */
    public function setLiveConsumerSecret($liveConsumerSecret)
    {
        $this->liveConsumerSecret = $liveConsumerSecret;
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
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     * @return Project
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return Project
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return IntegrationType
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param IntegrationType $group
     * @return Project
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Enrich the project with CultureFeed_Consumer data.
     */
    public function enrichWithConsumerInfo(\CultureFeed_Consumer $consumer)
    {
        $this->name = $consumer->name;
        $this->description = $consumer->description;
        $this->logo = $consumer->logo;
        $this->domain = $consumer->domain;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $json = [];

        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $json[$key] = $value;
            }
        }

        $json['created'] = $this->created->getTimestamp();
        $json['updated'] = $this->updated->getTimestamp();

        unset($json['groupId']);

        return $json;
    }

    /**
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updatedTimestamps()
    {
        $this->updated = new \DateTime();

        if (!$this->created) {
            $this->created = new \DateTime();
        }
    }
}
