<?php

namespace CultuurNet\ProjectAanvraag\Coupon\Exception;

use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;

class InvalidCouponException extends ValidationException
{
    const ERROR_CODE = 'INVALID_COUPON';

    public function getValidationCode()
    {
        return self::ERROR_CODE;
    }
}
