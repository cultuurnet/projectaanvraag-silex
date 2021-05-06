<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class TaxNumber
{
    /**
     * @var string
     */
    private $taxNumber;

    public function __construct(string $taxNumber)
    {
        $this->taxNumber = $taxNumber;
    }

    public function getValue(): string
    {
        return $this->taxNumber;
    }
}
