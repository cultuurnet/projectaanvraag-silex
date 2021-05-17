<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use InvalidArgumentException;

final class IntegrationType
{
    private const SEARCH_V3 = 'Publicatie Search API V3';
    private const ENTRY_V3 = 'Entry API V3';
    private const WIDGETS_V3 = 'Publicatie Widgets V3';

    private const ALLOWED_INTEGRATION_TYPES = [
        'SEARCH_V3' => self::SEARCH_V3,
        'ENTRY_V3' => self::ENTRY_V3,
        'WIDGETS_V3' => self::WIDGETS_V3,
    ];

    /**
     * @var string
     */
    private $integrationType;

    public function __construct(string $integrationType)
    {
        if (!in_array($integrationType, self::ALLOWED_INTEGRATION_TYPES, true)) {
            throw new InvalidArgumentException(
                'Encountered unsupported IntegrationType: ' . $integrationType . '. Allowed values: ' . implode(', ', self::ALLOWED_INTEGRATION_TYPES)
            );
        }

        $this->integrationType = $integrationType;
    }

    public function getValue(): string
    {
        return $this->integrationType;
    }

    public static function searchV3(): self
    {
        return new self(self::SEARCH_V3);
    }

    public static function entryV3(): self
    {
        return new self(self::ENTRY_V3);
    }

    public static function widgetsV3(): self
    {
        return new self(self::WIDGETS_V3);
    }

    public static function fromKey(string $key): IntegrationType
    {
        if (!isset(self::ALLOWED_INTEGRATION_TYPES[$key])) {
            throw new InvalidArgumentException(
                'Encountered unsupported IntegrationType key: ' . $key . '. Allowed values: ' . implode(', ', array_keys(self::ALLOWED_INTEGRATION_TYPES))
            );
        }

        return new self(self::ALLOWED_INTEGRATION_TYPES[$key]);
    }
}
