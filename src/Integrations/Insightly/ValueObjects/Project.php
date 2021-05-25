<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

use CultuurNet\SearchV3\ValueObjects\Status;

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
     * @var ?Coupon
     */
    private $coupon;

    public function __construct(
        Name $name,
        ProjectStage $stage,
        ProjectStatus $status,
        Description $description,
        IntegrationType $integrationType
    ) {
        $this->name = $name;
        $this->stage = $stage;
        $this->status = $status;
        $this->description = $description;
        $this->integrationType = $integrationType;
    }

    public function withId(Id $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    public function withCoupon(Coupon $coupon): self
    {
        $clone = clone $this;
        $clone->coupon = $coupon;
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

    public function updateStatus(ProjectStatus $status): Project
    {
        $clone = clone $this;
        $clone->status = $status;
        return $clone;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getIntegrationType(): IntegrationType
    {
        return $this->integrationType;
    }

    public function getCoupon(): ?Coupon
    {
        return $this->coupon;
    }
}
