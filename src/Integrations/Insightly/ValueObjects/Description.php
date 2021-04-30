<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class Description
{
    /**
     * @var string
     */
    private $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function getValue(): string
    {
        return $this->description;
    }
}
