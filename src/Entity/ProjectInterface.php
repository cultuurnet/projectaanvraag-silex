<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;

interface ProjectInterface extends EntityInterface, \JsonSerializable
{
    const PROJECT_STATUS_APPLICATION_SENT = 'application_sent';
    const PROJECT_STATUS_BLOCKED = 'blocked';
    const PROJECT_STATUS_ACTIVE = 'active';
    const PROJECT_STATUS_WAITING_FOR_PAYMENT = 'waiting_for_payment';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return ProjectInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getTestConsumerKey();

    /**
     * @param string $testConsumerKey
     * @return ProjectInterface
     */
    public function setTestConsumerKey($testConsumerKey);

    /**
     * @return string
     */
    public function getLiveConsumerKey();

    /**
     * @param string $liveConsumerKey
     * @return ProjectInterface
     */
    public function setLiveConsumerKey(string $liveConsumerKey);

    /**
     * Get the live search api 3 key.
     */
    public function getLiveApiKeySapi3();

    /**
     * Set the live search api 3 key.
     *
     * @param string $liveApiKeySapi3
     */
    public function setLiveApiKeySapi3(string $liveApiKeySapi3);

    /**
     * Get the test search api 3 key.
     */
    public function getTestApiKeySapi3();

    /**
     * Set the test search api 3 key.
     *
     * @param string $liveApiKeySapi3
     */
    public function setTestApiKeySapi3(string $testApiKeySapi3);

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $groupId
     * @return ProjectInterface
     */
    public function setGroupId($groupId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return ProjectInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param string $userId
     * @return ProjectInterface
     */
    public function setUserId($userId);

    /**
     * @return string
     */
    public function getInsightlyProjectId();

    /**
     * @param string $insightlyProjectId
     * @return ProjectInterface
     */
    public function setInsightlyProjectId($insightlyProjectId);

    public function getProjectIdInsightly(): ?int;

    public function setProjectIdInsightly(int $projectIdInsightly): ProjectInterface;

    public function getOpportunityIdInsightly(): ?int;

    public function setOpportunityIdInsightly(int $opportunityIdInsightly): ProjectInterface;

    /**
     * @return \DateTime
     */
    public function getCreated();

    /**
     * @param \DateTime $created
     * @return ProjectInterface
     */
    public function setCreated($created);

    /**
     * @return \DateTime
     */
    public function getUpdated();

    /**
     * @param \DateTime $updated
     * @return ProjectInterface
     */
    public function setUpdated($updated);

    /**
     * @return string
     */
    public function getTestConsumerSecret();

    /**
     * @param string $testConsumerSecret
     * @return ProjectInterface
     */
    public function setTestConsumerSecret($testConsumerSecret);

    /**
     * @return string
     */
    public function getLiveConsumerSecret();

    /**
     * @param string $liveConsumerSecret
     * @return ProjectInterface
     */
    public function setLiveConsumerSecret($liveConsumerSecret);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return ProjectInterface
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getPlatformUuid();

    /**
     * @param string $platformUuid
     * @return ProjectInterface
     */
    public function setPlatformUuid($platformUuid);

    /**
     * @return string
     */
    public function getCoupon();

    /**
     * @param string $coupon
     * @return Project
     */
    public function setCoupon($coupon);

    /**
     * @return string
     */
    public function getLogo();

    /**
     * @param string $logo
     * @return ProjectInterface
     */
    public function setLogo($logo);

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param string $domain
     * @return ProjectInterface
     */
    public function setDomain($domain);

    /**
     * @return IntegrationType
     */
    public function getGroup();

    /**
     * @param IntegrationType $group
     * @return ProjectInterface
     */
    public function setGroup($group);

    /**
     * @param string $contentFilter
     * @return ProjectInterface
     */
    public function setContentFilter($contentFilter);

    /**
     * @return string
     */
    public function getContentFilter();

    /**
     * @return string
     */
    public function getSapiVersion();

    /**
     * @param string $version
     * @return ProjectInterface
     */
    public function setSapiVersion($version);

    /**
     * @param int $totalWidgets
     */
    public function setTotalWidgets($totalWidgets);

    /**
     * @return int
     */
    public function getTotalWidgets();

    /**
     * Enrich the project with CultureFeed_Consumer data.
     * @param \CultureFeed_Consumer $consumer
     * @param string $version
     */
    public function enrichWithConsumerInfo(\CultureFeed_Consumer $consumer, string $version);
}
