<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use InvalidArgumentException;

final class ProjectStatus
{
    private const NOT_STARTED = 'Not Started';
    private const IN_PROGRESS = 'In Progress';
    private const DEFERRED = 'Deferred';
    private const CANCELLED = 'Cancelled';
    private const ABANDONED = 'Abandoned';
    private const COMPLETED = 'Completed';

    private const ALLOWED_STATUS = [
        self::NOT_STARTED,
        self::IN_PROGRESS,
        self::DEFERRED,
        self::CANCELLED,
        self::ABANDONED,
        self::COMPLETED
    ];

    /**
     * @var string
     */
    private $status;

    public function __construct(string $status)
    {
        if (!in_array($status, self::ALLOWED_STATUS, true)) {
            throw new InvalidArgumentException(
                'Encountered unsupported ProjectStatus: ' . $status . '. Allowed values: ' . implode(', ', self::ALLOWED_STATUS)
            );
        }

        $this->status = $status;
    }

    public function getValue(): string
    {
        return $this->status;
    }

    public static function notStarted(): self
    {
        return new self(self::NOT_STARTED);
    }

    public static function inProgress(): self
    {
        return new self(self::IN_PROGRESS);
    }

    public static function deferred(): self
    {
        return new self(self::DEFERRED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function abandoned(): self
    {
        return new self(self::ABANDONED);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }
}
