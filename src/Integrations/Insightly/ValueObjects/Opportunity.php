<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

class Opportunity
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
     * @var OpportunityState
     */
    private $state;

    /**
     * @var Description
     */
    private $description;

    public function __construct(Name $name, OpportunityState $state, Description $description)
    {
        $this->name = $name;
        $this->state = $state;
        $this->description = $description;
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
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

    public function getState(): OpportunityState
    {
        return $this->state;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }
}
