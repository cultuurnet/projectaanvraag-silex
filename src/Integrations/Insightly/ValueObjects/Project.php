<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class Project
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
     * @var ProjectStage
     */
    private $stage;

    /**
     * @var ProjectStatus
     */
    private $status;

    /**
     * @var Description
     */
    private $description;

    /**
     * @var IntegrationType
     */
    private $integrationType;

    /**
     * @var Coupon
     */
    private $coupon;

    /**
     * @var Id
     */
    private $contactId;

    public function __construct(
        Name $name,
        ProjectStage $stage,
        ProjectStatus $status,
        Description $description,
        IntegrationType $integrationType,
        Coupon $coupon,
        Id $contactId
    ) {
        $this->name = $name;
        $this->stage = $stage;
        $this->status = $status;
        $this->description = $description;
        $this->integrationType = $integrationType;
        $this->coupon = $coupon;
        $this->contactId = $contactId;
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

    public function getStage(): ProjectStage
    {
        return $this->stage;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getIntegrationType(): IntegrationType
    {
        return $this->integrationType;
    }

    public function getCoupon(): Coupon
    {
        return $this->coupon;
    }

    public function getContactId(): Id
    {
        return $this->contactId;
    }
}