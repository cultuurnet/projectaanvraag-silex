<?php

namespace CultuurNet\ProjectAanvraag\Widget\AgeFilter;

use CultuurNet\SearchV3\ValueObjects\BirthdateRange;
use CultuurNet\SearchV3\ValueObjects\Event;
use PHPUnit\Framework\TestCase;

class AgeFilterSelectionTest extends TestCase
{
    private const CURRENT_YEAR = 2026;

    /**
     * @dataProvider agesProvider
     */
    public function testItFindsTheSuitableAges(array $selectedAges, Event $event, array $expectedAges)
    {
        $ageFilter = AgeFilterSelection::fromAges($selectedAges);

        $this->assertSame($expectedAges, $ageFilter->findPartialMatch($event, self::CURRENT_YEAR));
    }

    public function agesProvider(): array
    {
        return [
            'one of the ages is suitable' => [[8, 12], $this->createEvent('6-10'), [8]],
            'several ages are suitable, sorted' => [[10, 6, 14], $this->createEvent('6-12'), [6, 10]],
            'no maximum age' => [[4, 8], $this->createEvent('6-'), [8]],
            'no minimum age' => [[4, 8], $this->createEvent('-6'), [4]],
            'only a birthdate range' => [[4, 10], $this->createEvent(null, '2020-01-01', '2024-12-31'), [4]],
            'all ages are suitable' => [[6, 8], $this->createEvent('6-10'), []],
            'no age is suitable' => [[14, 16], $this->createEvent('6-10'), []],
            'event for all ages' => [[8, 12], $this->createEvent('0-'), []],
            'event with an empty age range' => [[8, 12], $this->createEvent('-'), []],
            'event without age information' => [[8, 12], $this->createEvent(null), []],
        ];
    }

    /**
     * @dataProvider birthYearsProvider
     */
    public function testItFindsTheSuitableBirthYears(array $selectedBirthYears, Event $event, array $expectedBirthYears)
    {
        $ageFilter = AgeFilterSelection::fromBirthYears($selectedBirthYears);

        $this->assertSame($expectedBirthYears, $ageFilter->findPartialMatch($event, self::CURRENT_YEAR));
    }

    public function birthYearsProvider(): array
    {
        return [
            'one birth year overlaps' => [[2016, 2020], $this->createEvent(null, '2018-01-01', '2022-12-31'), [2020]],
            'birth year contains the whole range' => [[2016, 2020], $this->createEvent(null, '2020-07-01', '2020-08-31'), [2020]],
            'only a first birthdate' => [[2016, 2020], $this->createEvent(null, '2018-01-01'), [2020]],
            'dates with a time and offset' => [[2016, 2018], $this->createEvent(null, '2018-01-01T00:00:00+00:00', '2022-12-31T00:00:00+00:00'), [2018]],
            'swapped dates' => [[2016, 2020], $this->createEvent(null, '2022-12-31', '2018-01-01'), [2020]],
            'birthdate range wins over age range' => [[2016, 2020], $this->createEvent('6-10', '2018-01-01', '2022-12-31'), [2020]],
            'only an age range' => [[2018, 2012], $this->createEvent('6-10'), [2018]],
            'all birth years are suitable' => [[2018, 2020], $this->createEvent(null, '2018-01-01', '2022-12-31'), []],
            'event without age information' => [[2016, 2020], $this->createEvent(null), []],
        ];
    }

    public function testItSortsTheSelectedValues()
    {
        $this->assertSame([6, 10, 14], AgeFilterSelection::fromAges([10, 6, 14])->getValues());
    }

    private function createEvent(?string $typicalAgeRange, ?string $firstBirthdate = null, ?string $lastBirthdate = null): Event
    {
        $event = new Event();

        if ($typicalAgeRange !== null) {
            $event->setTypicalAgeRange($typicalAgeRange);
        }

        if ($firstBirthdate !== null) {
            $event->setBirthdateRange(new BirthdateRange($firstBirthdate, $lastBirthdate));
        }

        return $event;
    }
}
