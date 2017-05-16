<?php

namespace CultuurNet\ProjectAanvraag;

/**
 * Password generator
 */
trait PasswordGeneratorTrait
{

    /**
     * Generate a password.
     * @param int $length
     * @return string
     */
    public function generatePassword($length = 20)
    {
        return bin2hex(openssl_random_pseudo_bytes($length / 2));
    }
}
