<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

class ImportProject
{
    /**
     * @var string
     */
    private $userId;

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
    private $groupId;

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
     * @var string
     */
    private $testClientId;

    /**
     * @var string
     */
    private $liveClientId;

    /**
     * @var string
     */
    private $state;

    public function __construct(
        string $platformUuid,
        string $userId,
        string $name,
        string $description,
        int $groupId,
        string $testApiKeySapi3,
        string $liveApiKeySapi3,
        string $state
    ) {
        $this->platformUuid = $platformUuid;
        $this->userId = $userId;
        $this->name = $name;
        $this->description = $description;
        $this->groupId = $groupId;
        $this->testApiKeySapi3 = $testApiKeySapi3;
        $this->liveApiKeySapi3 = $liveApiKeySapi3;
        $this->state = $state;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getPlatformUuid(): string
    {
        return $this->platformUuid;
    }

    public function getTestApiKeySapi3(): string
    {
        return $this->testApiKeySapi3;
    }

    public function getLiveApiKeySapi3(): string
    {
        return $this->liveApiKeySapi3;
    }

    public function getTestClientId(): string
    {
        return $this->testClientId;
    }

    public function getLiveClientId(): string
    {
        return $this->liveClientId;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
