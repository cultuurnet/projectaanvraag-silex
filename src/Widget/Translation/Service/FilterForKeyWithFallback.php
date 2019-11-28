<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Widget\Translation\Service;

class FilterForKeyWithFallback
{
    /**
     * @var string
     */
    private $fallBackLanguage;

    public function __construct(string $fallBackLanguage)
    {
        $this->fallBackLanguage = $fallBackLanguage;
    }

    public function __invoke(array $values, string $preferredLanguage)
    {
        if (empty($values)) {
            return [];
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
