<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class LastName
{
    /**
     * @var string
     */
    private $lastName;

    public function __construct(string $lastName)
    {
        $this->lastName = $lastName;
    }

    public function getValue(): string
    {
        return $this->lastName;
    }
}
