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

    public function __invoke(array $values, $preferredLanguage, $mainLanguage = 'nl')
    {
        if (empty($values)) {
            return [];
        }

        if (!$this->hasPreferred($values, $preferredLanguage)) {
            if (isset($values[$this->fallBackLanguage])) {
              return $values[$this->fallBackLanguage];
            } elseif (isset($values[$mainLanguage])) {
              return $values[$mainLanguage];
            } else {
              $firstKey = array_keys($values)[0];
              return $values[$firstKey];
            }
        }

        return $values[$preferredLanguage];
    }

    protected function hasPreferred(array $values, $preferredLanguage): bool
    {
        return isset($values[$preferredLanguage]);
    }
}
