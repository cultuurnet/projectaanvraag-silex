<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class FirstName
{
    /**
     * @var string
     */
    private $fistName;

    public function __construct(string $fistName)
    {
        $this->fistName = $fistName;
    }

    public function getValue(): string
    {
        return $this->fistName;
    }
}
