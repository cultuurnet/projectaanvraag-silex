<?php

namespace CultuurNet\ProjectAanvraag\Widget\AgeFilter;

use CultuurNet\SearchV3\ValueObjects\BirthdateRange;

final class BirthYearRange
{
    private const YEAR_LENGTH = 4;

    /**
     * @var int|null
     */
    private $firstBirthYear;

    /**
     * @var int|null
     */
    private $lastBirthYear;

    private function __construct(?int $firstBirthYear, ?int $lastBirthYear)
    {
        $this->firstBirthYear = $firstBirthYear;
        $this->lastBirthYear = $lastBirthYear;
    }

    public static function fromBirthdateRange(?BirthdateRange $birthdateRange): ?self
    {
        if ($birthdateRange === null) {
            return null;
        }

        $firstBirthYear = self::extractYear($birthdateRange->getFrom());
        $lastBirthYear = self::extractYear($birthdateRange->getTo());

        if ($firstBirthYear === null && $lastBirthYear === null) {
            return null;
        }

        if ($firstBirthYear !== null && $lastBirthYear !== null) {
            return new self(min($firstBirthYear, $lastBirthYear), max($firstBirthYear, $lastBirthYear));
        }

        return new self($firstBirthYear, $lastBirthYear);
    }

    public function contains(int $birthYear): bool
    {
        $isBornLateEnough = $this->firstBirthYear === null || $birthYear >= $this->firstBirthYear;
        $isBornEarlyEnough = $this->lastBirthYear === null || $birthYear <= $this->lastBirthYear;

        return $isBornLateEnough && $isBornEarlyEnough;
    }

    private static function extractYear(?string $date): ?int
    {
        $year = (string) substr(trim((string) $date), 0, self::YEAR_LENGTH);

        return ctype_digit($year) ? (int) $year : null;
    }
}
