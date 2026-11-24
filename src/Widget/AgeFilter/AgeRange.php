<?php

namespace CultuurNet\ProjectAanvraag\Widget\AgeFilter;

final class AgeRange
{
    /**
     * @var int
     */
    private $minimumAge;

    /**
     * @var int|null
     */
    private $maximumAge;

    private function __construct(int $minimumAge, ?int $maximumAge)
    {
        $this->minimumAge = $minimumAge;
        $this->maximumAge = $maximumAge;
    }

    public static function fromTypicalAgeRange(?string $typicalAgeRange): ?self
    {
        $typicalAgeRange = trim((string) $typicalAgeRange);
        if ($typicalAgeRange === '') {
            return null;
        }

        list($minimumAge, $maximumAge) = explode('-', $typicalAgeRange) + ['', ''];

        return new self((int) $minimumAge, $maximumAge === '' ? null : (int) $maximumAge);
    }

    public function isForAllAges(): bool
    {
        return $this->minimumAge === 0 && $this->maximumAge === null;
    }

    public function contains(int $age): bool
    {
        $isOldEnough = $age >= $this->minimumAge;
        $isYoungEnough = $this->maximumAge === null || $age <= $this->maximumAge;

        return $isOldEnough && $isYoungEnough;
    }
}
