<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use InvalidArgumentException;

final class ProjectStage
{
    private const TEST = 'test';
    private const START = 'start';
    private const EVALUATION = 'evaluation';
    private const LIVE = 'live';
    private const ENDED = 'ended';

    private const ALLOWED_STAGES = [
        self::TEST,
        self::START,
        self::EVALUATION,
        self::LIVE,
        self::ENDED,
    ];

    /**
     * @var string
     */
    private $state;

    public function __construct(string $stage)
    {
        if (!in_array($stage, self::ALLOWED_STAGES, true)) {
            throw new InvalidArgumentException(
                'Encountered unsupported ProjectStage: ' . $stage . '. Allowed values: ' . implode(', ', self::ALLOWED_STAGES)
            );
        }

        $this->state = $stage;
    }

    public function getValue(): string
    {
        return $this->state;
    }

    public static function test(): self
    {
        return new self(self::TEST);
    }

    public static function start(): self
    {
        return new self(self::START);
    }

    public static function evaluation(): self
    {
        return new self(self::EVALUATION);
    }

    public static function live(): self
    {
        return new self(self::LIVE);
    }

    public static function ended(): self
    {
        return new self(self::ENDED);
    }
}
