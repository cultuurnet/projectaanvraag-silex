<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use Exception;

final class CustomFieldNotFound extends Exception
{
    public static function forKey(string $key): CustomFieldNotFound
    {
        return new self('No custom field found with key: ' . $key);
    }
}
