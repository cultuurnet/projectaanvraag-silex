<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class Address
{
    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $postal;

    /**
     * @var string
     */
    private $city;

    public function __construct(string $street, string $postal, string $city)
    {
        $this->street = $street;
        $this->postal = $postal;
        $this->city = $city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostal(): string
    {
        return $this->postal;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}
