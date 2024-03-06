<?php

namespace CultuurNet\ProjectAanvraag\Coupon;

interface CouponValidatorInterface
{

    /**
     * Validate if the given coupon is correct.
     * @param $coupon
     *   Coupon to validate
     * @return void
     * @throws InvalidCouponException
     * @throws CouponInUseException
     */
    public function validateCoupon($coupon);
}
