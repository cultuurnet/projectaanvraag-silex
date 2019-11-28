<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

use CultuurNet\SearchV3\ValueObjects\TranslatedString;

class TranslateWithFallback
{
    /**
     * @var string
     */
    private $fallBackLanguage;

    public function __construct(string $fallBackLanguage)
    {
        $this->fallBackLanguage = $fallBackLanguage;
    }

    public function __invoke(TranslatedString $string, string $preferredLanguage): string
    {
        $values = $string->getValues();

        if (empty($values)) {
            return '';
        }

        if (!$this->hasPreferred($values, $preferredLanguage)) {
            return $values[$this->fallBackLanguage];
        }
        return $values[$preferredLanguage];
    }

    protected function hasPreferred(array $values, string $preferredLanguage): bool
    {
        return isset($values[$preferredLanguage]);
    }
}
