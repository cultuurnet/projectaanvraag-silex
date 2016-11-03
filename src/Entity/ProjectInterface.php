<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;

interface ProjectInterface extends EntityInterface, \JsonSerializable
{
    const PROJECT_STATUS_APPLICATION_SENT = 'application_sent';
    const PROJECT_STATUS_BLOCKED = 'blocked';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return Project
     */
    public function setId($id);
    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return Project
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getTestConsumerKey();

    /**
     * @param string $testConsumerKey
     * @return Project
     */
    public function setTestConsumerKey($testConsumerKey);

    /**
     * @return string
     */
    public function getLiveConsumerKey();

    /**
     * @param string $liveConsumerKey
     * @return Project
     */
    public function setLiveConsumerKey($liveConsumerKey);

    /**
     * @return int
     */
    public function getGroupId();

    /**
     * @param int $groupId
     * @return Project
     */
    public function setGroupId($groupId);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return Project
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @param string $userId
     * @return Project
     */
    public function setUserId($userId);

    /**
     * @return string
     */
    public function getInsightlyProjectId();

    /**
     * @param string $insightlyProjectId
     * @return Project
     */
    public function setInsightlyProjectId($insightlyProjectId);

    /**
     * @return \DateTime
     */
    public function getCreated();

    /**
     * @param \DateTime $created
     * @return Project
     */
    public function setCreated($created);

    /**
     * @return \DateTime
     */
    public function getUpdated();

    /**
     * @param \DateTime $updated
     * @return Project
     */
    public function setUpdated($updated);

    /**
     * @return string
     */
    public function getTestConsumerSecret();

    /**
     * @param string $testConsumerSecret
     * @return Project
     */
    public function setTestConsumerSecret($testConsumerSecret);

    /**
     * @return string
     */
    public function getLiveConsumerSecret();

    /**
     * @param string $liveConsumerSecret
     * @return Project
     */
    public function setLiveConsumerSecret($liveConsumerSecret);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return Project
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getLogo();

    /**
     * @param string $logo
     * @return Project
     */
    public function setLogo($logo);

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @param string $domain
     * @return Project
     */
    public function setDomain($domain);

    /**
     * @return IntegrationType
     */
    public function getGroup();

    /**
     * @param IntegrationType $group
     * @return Project
     */
    public function setGroup($group);

    /**
     * Enrich the project with CultureFeed_Consumer data.
     * @param \CultureFeed_Consumer $consumer
     * @return
     */
    public function enrichWithConsumerInfo(\CultureFeed_Consumer $consumer);
}
