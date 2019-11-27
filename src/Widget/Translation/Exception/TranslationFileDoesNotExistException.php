<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Exception;

class TranslationFileDoesNotExistException extends \Exception
{
    public static function inFolder(string $path)
    {
        return new TranslationFileDoesNotExistException('No translation file present in folder:' . $path);
    }
}
