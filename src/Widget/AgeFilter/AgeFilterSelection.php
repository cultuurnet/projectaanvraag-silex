<?php

namespace CultuurNet\ProjectAanvraag\Widget\AgeFilter;

use CultuurNet\SearchV3\ValueObjects\Event;

final class AgeFilterSelection
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @var bool
     */
    private $isBirthYears;

    /**
     * @param int[] $values
     */
    private function __construct(array $values, bool $isBirthYears)
    {
        sort($values);

        $this->values = $values;
        $this->isBirthYears = $isBirthYears;
    }

    /**
     * @param int[] $ages
     */
    public static function fromAges(array $ages): self
    {
        return new self($ages, false);
    }

    /**
     * @param int[] $birthYears
     */
    public static function fromBirthYears(array $birthYears): self
    {
        return new self($birthYears, true);
    }

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function isBirthYears(): bool
    {
        return $this->isBirthYears;
    }

    /**
     * @return int[]
     */
    public function findPartialMatch(Event $event, int $currentYear): array
    {
        $ageRange = AgeRange::fromTypicalAgeRange($event->getTypicalAgeRange());
        $birthYearRange = BirthYearRange::fromBirthdateRange($event->getBirthdateRange());

        $eventHasNoAgeInformation = $ageRange === null && $birthYearRange === null;
        $eventIsForAllAges = $ageRange !== null && $ageRange->isForAllAges();
        if ($eventHasNoAgeInformation || $eventIsForAllAges) {
            return [];
        }

        $suitableValues = [];
        foreach ($this->values as $value) {
            $isSuitable = $this->isBirthYears
                ? $this->isSuitableBirthYear($value, $ageRange, $birthYearRange, $currentYear)
                : $this->isSuitableAge($value, $ageRange, $birthYearRange, $currentYear);

            if ($isSuitable) {
                $suitableValues[] = $value;
            }
        }

        $allValuesAreSuitable = count($suitableValues) === count($this->values);

        return $allValuesAreSuitable ? [] : $suitableValues;
    }

    private function isSuitableAge(int $age, ?AgeRange $ageRange, ?BirthYearRange $birthYearRange, int $currentYear): bool
    {
        if ($ageRange !== null) {
            return $ageRange->contains($age);
        }

        return $birthYearRange->contains($currentYear - $age);
    }

    private function isSuitableBirthYear(int $birthYear, ?AgeRange $ageRange, ?BirthYearRange $birthYearRange, int $currentYear): bool
    {
        if ($birthYearRange !== null) {
            return $birthYearRange->contains($birthYear);
        }

        return $ageRange->contains($currentYear - $birthYear);
    }
}
