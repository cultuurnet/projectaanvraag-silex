<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use InvalidArgumentException;

final class OpportunityStage
{
    private const TEST = 'test';
    private const REQUEST = 'request';
    private const INFORMATION = 'information';
    private const OFFER = 'offer';
    private const CLOSED = 'closed';

    private const ALLOWED_STAGES = [
        self::TEST,
        self::REQUEST,
        self::INFORMATION,
        self::OFFER,
        self::CLOSED,
    ];

    /**
     * @var string
     */
    private $state;

    public function __construct(string $stage)
    {
        if (!in_array($stage, self::ALLOWED_STAGES, true)) {
            throw new InvalidArgumentException(
                'Encountered unsupported OpportunityStage: ' . $stage . '. Allowed values: ' . implode(', ', self::ALLOWED_STAGES)
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

    public static function request(): self
    {
        return new self(self::REQUEST);
    }

    public static function information(): self
    {
        return new self(self::INFORMATION);
    }

    public static function offer(): self
    {
        return new self(self::OFFER);
    }

    public static function closed(): self
    {
        return new self(self::CLOSED);
    }
}
