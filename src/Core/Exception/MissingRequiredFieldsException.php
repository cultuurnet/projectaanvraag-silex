<?php

namespace CultuurNet\ProjectAanvraag\Core\Exception;

class MissingRequiredFieldsException extends ValidationException
{
    const ERROR_CODE = 'MISSING_REQUIRED_FIELDS';

    public function getValidationCode()
    {
        return self::ERROR_CODE;
    }
}
