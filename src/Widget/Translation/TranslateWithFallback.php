<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation;

use CultuurNet\SearchV3\ValueObjects\TranslatedString;

class TranslateWithFallback
{
    /**
     * @var string
     */
    private $preferredLanguage;
    /**
     * @var string
     */
    private $fallBackLanguage;

    public function __construct(string $preferredLanguage, string $fallBackLanguage)
    {
        $this->preferredLanguage = $preferredLanguage;
        $this->fallBackLanguage = $fallBackLanguage;
    }

    public function __invoke(TranslatedString $string): string
    {
        $values = $string->getValues();

        if (empty($values)) {
            return '';
        }

        if (!$this->hasPreferred($values)) {
            return $values[$this->fallBackLanguage];
        }

        return $values[$this->preferredLanguage];
    }

    /**
     * @param array $values
     * @return bool
     */
    protected function hasPreferred(array $values): bool
    {
        return isset($values[$this->preferredLanguage]);
    }
}
