<?php

namespace CultuurNet\ProjectAanvraag\Coupon\Exception;

use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;

class CouponInUseException extends ValidationException
{
    const ERROR_CODE = 'COUPON_ALREADY_USED';

    public function getValidationCode()
    {
        return self::ERROR_CODE;
    }
}
