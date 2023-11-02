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

    public function __construct(
        string $platformUuid,
        string $name,
        string $description,
        int $groupId,
        string $testApiKeySapi3,
        string $liveApiKeySapi3
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->groupId = $groupId;
        $this->platformUuid = $platformUuid;
        $this->testApiKeySapi3 = $testApiKeySapi3;
        $this->liveApiKeySapi3 = $liveApiKeySapi3;
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
}
