<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects;

final class Coupon
{
    /**
     * @var string
     */
    private $coupon;

    public function __construct(string $coupon)
    {
        $this->coupon = $coupon;
    }

    public function getValue(): string
    {
        return $this->coupon;
    }
}
