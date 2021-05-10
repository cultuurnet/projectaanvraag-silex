<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class Organization
{
    /**
     * @var ?Id
     */
    private $id;

    /**
     * @var Name
     */
    private $name;

    /**
     * @var Address
     */
    private $address;

    /**
     * @var Email
     */
    private $email;

    /**
     * @var ?TaxNumber
     */
    private $taxNumber;

    public function __construct(Name $name, Address $address, Email $email)
    {
        $this->name = $name;
        $this->address = $address;
        $this->email = $email;
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withTaxNumber(TaxNumber $taxNumber): self
    {
        $clone = clone $this;
        $clone->taxNumber = $taxNumber;
        return $clone;
    }

    public function getId(): ?Id
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getTaxNumber(): ?TaxNumber
    {
        return $this->taxNumber;
    }
}
