<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use InvalidArgumentException;

final class OpportunityState
{
    private const ABANDONED = 'Abandoned';
    private const LOST = 'Lost';
    private const SUSPENDED = 'Suspended';
    private const WON = 'Won';
    private const OPEN = 'Open';

    const ALLOWED_STATES = [
        self::ABANDONED,
        self::LOST,
        self::SUSPENDED,
        self::WON,
        self::OPEN,
    ];

    /**
     * @var string
     */
    private $state;

    public function __construct(string $state)
    {
        if (!in_array($state, self::ALLOWED_STATES, true)) {
            throw new InvalidArgumentException(
                'Encountered unsupported OpportunityState: ' . $state . '. Allowed values: ' . implode(', ', self::ALLOWED_STATES)
            );
        }

        $this->state = $state;
    }

    public function getValue(): string
    {
        return $this->state;
    }

    public static function abandoned(): self
    {
        return new self(self::ABANDONED);
    }

    public static function lost(): self
    {
        return new self(self::LOST);
    }

    public static function suspended(): self
    {
        return new self(self::SUSPENDED);
    }

    public static function won(): self
    {
        return new self(self::WON);
    }

    public static function open(): self
    {
        return new self(self::OPEN);
    }
}
