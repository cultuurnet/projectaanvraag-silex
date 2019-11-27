<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Exception;

class InvalidTranslationFileException extends \Exception
{
    public static function forPath(string $path)
    {
        return new InvalidTranslationFileException('Invalid translation file :' . $path);
    }
}
