<?php

namespace CultuurNet\ProjectAanvraag\Core\Exception;

/**
 * Abstract exception class for < PHP 7 support. Can be replaced with interface when 5.6 support is dropped.
 */
abstract class ValidationException extends \Exception
{
    /**
     * @return string
     */
    public function getValidationCode() {
        return;
    }
}
