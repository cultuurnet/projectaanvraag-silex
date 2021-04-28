<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(
 *     name="project",
 *     indexes={
 *         @ORM\Index(name="live_uid", columns={"live_uid"}),
 *         @ORM\Index(name="test_uid", columns={"test_uid"}),
 *         @ORM\Index(name="test_consumer_key", columns={"test_consumer_key"}),
 *         @ORM\Index(name="live_consumer_key", columns={"live_consumer_key"}),
 *     }
 * )
 */
class Project implements ProjectInterface
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Type("integer")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="live_uid", type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $userId;

    /**
     * @ORM\Column(name="test_uid", type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $testUserId;

    /**
     * @ORM\Column(name="insightly_project_id", type="integer", nullable=true)
     * @Type("integer")
     * @var string
     */
    protected $insightlyProjectId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $name;


    /**
     * @ORM\Column(name="test_consumer_key", type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $testConsumerKey;

    /**
     * @ORM\Column(name="live_consumer_key", type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $liveConsumerKey;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Type("integer")
     * @var int
     */
    protected $groupId;

    /**
     * @ORM\Column(type="string", length=255)
     * @Type("string")
     * @var string
     */
    protected $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Type("string")
     * @var string
     */
    protected $coupon;

    /**
     * @ORM\Column(type="datetime")
     * @Type("DateTime")
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @Type("DateTime")
     * @var \DateTime
     */
    protected $updated;

    /**
     * @var string
     * @Type("string")
     */
    protected $testConsumerSecret;

    /**
     * @var string
     * @Type("string")
     */
    protected $liveConsumerSecret;

    /**
     * The search api 3 key for this project.
     *
     * @ORM\Column(name="live_search_api3_key", type="string", length=255, nullable=true)
     * @var string
     * @Type("string")
     */
    protected $liveApiKeySapi3;

    /**
     * The search api 3 key for this project.
     *
     * @ORM\Column(name="test_search_api3_key", type="string", length=255, nullable=true)
     * @var string
     * @Type("string")
     */
    protected $testApiKeySapi3;

    /**
     * @var string
     * @Type("string")
     */
    protected $description;

    /**
     * @var string
     * @Type("string")
     */
    protected $logo;

    /**
     * @var string
     * @Type("string")
     */
    protected $domain;

    /**
     * @var IntegrationType
     * @Exclude
     */
    protected $group;

    /**
     * @var string
     * @Type("string")
     */
    protected $contentFilter;

    /**
     * The total widgets connected with this project.
     * @var int
     * @Type("integer")
     */
    protected $totalWidgets;

    /**
     * @var string
     * @Type("string")
     */
    protected $sapiVersion;

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
    public function setLiveConsumerKey(string $liveConsumerKey)
    {
        $this->liveConsumerKey = $liveConsumerKey;
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
     * @return Project
     */
    public function setLiveApiKeySapi3(string $liveApiKeySapi3)
    {
        $this->liveApiKeySapi3 = $liveApiKeySapi3;
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
     * @return Project
     */
    public function setTestApiKeySapi3(string $testApiKeySapi3): Project
    {
        $this->testApiKeySapi3 = $testApiKeySapi3;
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
     * @return string
     */
    public function getInsightlyProjectId()
    {
        return $this->insightlyProjectId;
    }

    /**
     * @param string $insightlyProjectId
     * @return Project
     */
    public function setInsightlyProjectId($insightlyProjectId)
    {
        $this->insightlyProjectId = $insightlyProjectId;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentFilter()
    {
        return $this->contentFilter;
    }

    /**
     * @param string $contentFilter
     * @return Project
     */
    public function setContentFilter($contentFilter)
    {
        $this->contentFilter = $contentFilter;
    }

    /**
     * @return string
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * @param string $coupon
     * @return Project
     */
    public function setCoupon($coupon)
    {
        $this->coupon = $coupon;
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
     * @param string $coupon
     * @return Project
     */
    public function setSapiVersion($version)
    {
        $this->sapiVersion = $version;
        return $this;
    }

    public function setTotalWidgets($totalWidgets)
    {
        $this->totalWidgets = $totalWidgets;
        return $this;
    }

    public function getTotalWidgets()
    {
        return $this->totalWidgets;
    }

    /**
     * Enrich the project with CultureFeed_Consumer data.
     */
    public function enrichWithConsumerInfo(\CultureFeed_Consumer $consumer, string $version = "2")
    {
        $this->name = str_replace('[TEST] ', '', $consumer->name);
        $this->description = $consumer->description;
        $this->logo = $consumer->logo;
        $this->domain = $consumer->domain;
        if ($version == "3") {
            $this->contentFilter = $consumer->searchPrefixSapi3;
        } else {
            $this->contentFilter = $consumer->searchPrefixFilterQuery;
        }
    }

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

        // @@phpstan-ignore-next-line
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
