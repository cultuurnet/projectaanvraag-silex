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
    public function setLiveConsumerKey($liveConsumerKey);

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
     * Enrich the project with CultureFeed_Consumer data.
     * @param \CultureFeed_Consumer $consumer
     */
    public function enrichWithConsumerInfo(\CultureFeed_Consumer $consumer);
}
