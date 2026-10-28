<?php

namespace CultuurNet\ProjectAanvraag\Widget\Twig;

use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\ProjectAanvraag\Uitpas\UitpasClient;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\FilterForKeyWithFallback;
use CultuurNet\ProjectAanvraag\Widget\Translation\Service\TranslateTerm;
use CultuurNet\SearchV3\ValueObjects\Childcare;
use CultuurNet\SearchV3\ValueObjects\Event;
use CultuurNet\SearchV3\ValueObjects\Offer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\TranslatorInterface;

class TwigPreprocessorPracticalHoursTest extends TestCase
{
    /**
     * @var TwigPreprocessor
     */
    private $twigPreprocessor;

    public function setUp()
    {
        $trans = function ($id, array $parameters) {
            return isset($parameters['%time%']) ? $id . ' ' . $parameters['%time%'] : $id;
        };
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback($trans);

        $this->twigPreprocessor = new TwigPreprocessor(
            $this->createMock(\Twig_Environment::class),
            new RequestStack(),
            $this->createMock(\CultureFeed::class),
            'https://social.example',
            $this->createMock(FilterForKeyWithFallback::class),
            $this->createMock(TranslateTerm::class),
            $translator,
            $this->createMock(CuratorenClient::class),
            $this->createMock(UitpasClient::class),
            false
        );
    }

    /**
     * @dataProvider practicalHoursProvider
     */
    public function testFormatPracticalHours(string $calendarType, array $subEvents, ?string $expectedActivity, ?array $expectedChildcare)
    {
        $event = new Event();
        $event->setCalendarType($calendarType);
        $event->setSubEvents(array_map([$this, 'createSubEvent'], $subEvents));

        $method = new \ReflectionMethod(TwigPreprocessor::class, 'formatPracticalHours');
        $method->setAccessible(true);

        $this->assertSame(
            ['activity' => $expectedActivity, 'childcare' => $expectedChildcare],
            $method->invoke($this->twigPreprocessor, $event, 'nl')
        );
    }

    public function practicalHoursProvider(): array
    {
        return [
            'single' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', '08:00', '17:30']],
                '10:00 - 16:00',
                ['label' => 'event_hours_childcare', 'hours' => '08:00 - 17:30'],
            ],
            'single without childcare' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', null, null]],
                '10:00 - 16:00',
                null,
            ],
            'only vooropvang' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', '08:00', null]],
                '10:00 - 16:00',
                ['label' => 'event_hours_childcare_before', 'hours' => 'event_hours_from 08:00'],
            ],
            'only naopvang' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', null, '17:30']],
                '10:00 - 16:00',
                ['label' => 'event_hours_childcare_after', 'hours' => 'event_hours_until 17:30'],
            ],
            'multiple uses earliest start and latest end' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-21T10:00:00+02:00', '2026-10-21T15:00:00+02:00', '08:30', '16:00'],
                    ['2026-10-22T09:00:00+02:00', '2026-10-22T16:00:00+02:00', '08:00', '17:00'],
                    ['2026-10-23T11:00:00+02:00', '2026-10-23T12:00:00+02:00', null, null],
                ],
                '09:00 - 16:00',
                ['label' => 'event_hours_childcare', 'hours' => '08:00 - 17:00'],
            ],
            'multiple with vooropvang and naopvang on different days' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', '08:00', null],
                    ['2026-10-22T10:00:00+02:00', '2026-10-22T16:00:00+02:00', null, '17:30'],
                ],
                '10:00 - 16:00',
                ['label' => 'event_hours_childcare', 'hours' => '08:00 - 17:30'],
            ],
            'converts to Brussels time' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-12-01T09:00:00+00:00', '2026-12-01T15:00:00+00:00', null, null]],
                '10:00 - 16:00',
                null,
            ],
            'whole day has no activity hours' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T00:00:00+02:00', '2026-10-21T23:59:59+02:00', '08:00', '17:00']],
                null,
                ['label' => 'event_hours_childcare', 'hours' => '08:00 - 17:00'],
            ],
            'past midnight is the latest end hour' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-21T10:00:00+02:00', '2026-10-21T12:00:00+02:00', null, null],
                    ['2026-10-22T20:00:00+02:00', '2026-10-23T02:00:00+02:00', null, null],
                ],
                '10:00 - 02:00',
                null,
            ],
            'only past midnight' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T20:00:00+02:00', '2026-10-22T02:00:00+02:00', null, null]],
                '20:00 - 02:00',
                null,
            ],
            'multiple past midnight uses latest next day end' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-21T21:00:00+02:00', '2026-10-22T01:00:00+02:00', null, null],
                    ['2026-10-22T20:00:00+02:00', '2026-10-23T03:00:00+02:00', null, null],
                ],
                '20:00 - 03:00',
                null,
            ],
            'only start time' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T10:00:00+02:00', null, null]],
                'event_hours_at 10:00',
                null,
            ],
            'multiple with only start times on different hours' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-21T10:00:00+02:00', '2026-10-21T10:00:00+02:00', null, null],
                    ['2026-10-22T14:00:00+02:00', '2026-10-22T14:00:00+02:00', null, null],
                ],
                '10:00 - 14:00',
                null,
            ],
            'single over several days shows weekdays' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-23T18:00:00+02:00', '2026-10-25T23:00:00+01:00', null, null]],
                'friday_short 18:00 - sunday_short 23:00',
                null,
            ],
            'single over several days with end before start shows weekdays' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-19T14:00:00+02:00', '2026-10-23T12:00:00+02:00', null, null]],
                'monday_short 14:00 - friday_short 12:00',
                null,
            ],
            'single over several whole days has no activity hours' => [
                Offer::CALENDAR_TYPE_SINGLE,
                [['2026-10-23T00:00:00+02:00', '2026-10-25T23:59:59+01:00', null, null]],
                null,
                null,
            ],
            'multiple with a date over several days has no activity hours' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [['2026-10-19T14:00:00+02:00', '2026-10-23T12:00:00+02:00', null, null]],
                null,
                null,
            ],
            'multiple ignores the start of a date over several days' => [
                Offer::CALENDAR_TYPE_MULTIPLE,
                [
                    ['2026-10-22T10:00:00+02:00', '2026-10-23T02:00:00+02:00', '00:30', '15:15'],
                    ['2026-10-27T10:00:00+01:00', '2026-10-31T02:00:00+01:00', '04:30', '04:45'],
                    ['2026-11-01T04:00:00+01:00', '2026-11-11T02:00:00+01:00', '00:45', '15:15'],
                ],
                '10:00 - 02:00',
                ['label' => 'event_hours_childcare', 'hours' => '00:30 - 15:15'],
            ],
            'periodic has no hours' => [
                Offer::CALENDAR_TYPE_PERIODIC,
                [['2026-10-21T10:00:00+02:00', '2026-10-21T16:00:00+02:00', '08:00', '17:00']],
                null,
                null,
            ],
        ];
    }

    private function createSubEvent(array $values): Event
    {
        [$startDate, $endDate, $childcareStart, $childcareEnd] = $values;

        $subEvent = new Event();
        $subEvent->setStartDate(new \DateTime($startDate));
        $subEvent->setEndDate(new \DateTime($endDate));
        if ($childcareStart !== null || $childcareEnd !== null) {
            $subEvent->setChildcare(new Childcare($childcareStart, $childcareEnd));
        }

        return $subEvent;
    }
}
