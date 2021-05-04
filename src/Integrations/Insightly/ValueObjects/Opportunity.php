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
     * @var OpportunityStage
     */
    private $stage;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var IntegrationType
     */
    private $integrationType;

    public function __construct(
        Name $name,
        OpportunityState $state,
        OpportunityStage $stage,
        Description $description,
        IntegrationType $integrationType
    ) {
        $this->name = $name;
        $this->state = $state;
        $this->stage = $stage;
        $this->description = $description;
        $this->integrationType = $integrationType;
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

    public function getStage(): OpportunityStage
    {
        return $this->stage;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getIntegrationType(): IntegrationType
    {
        return $this->integrationType;
    }
}
