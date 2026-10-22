<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\Event\SearchResultsQueryAlter;
use CultuurNet\ProjectAanvraag\Widget\RegionService;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\SearchQuery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SearchFormDatePickerTest extends TestCase
{
    /**
     * Test if the selected days and times are added to the search query.
     *
     * @dataProvider datePickerProvider
     */
    public function testAlterSearchResultsQuery(array $params, array $expectedQuery, array $expectedLabels)
    {
        $searchResultsQueryAlter = new SearchResultsQueryAlter('1', new SearchQuery(), []);
        $this->createSearchForm($params)->alterSearchResultsQuery($searchResultsQueryAlter);

        $this->assertSame($expectedQuery, $searchResultsQueryAlter->getSearchQuery()->toArray());
        $this->assertSame($expectedLabels, array_column($searchResultsQueryAlter->getActiveFilters(), 'label'));
    }

    public function datePickerProvider(): array
    {
        return [
            'days' => [
                ['days' => 'sunday|monday|foo|monday'],
                ['recurringOnDayOfWeek' => 'monday,sunday'],
                ['monday, sunday'],
            ],
            'every day' => [
                ['days' => 'monday|tuesday|wednesday|thursday|friday|saturday|sunday'],
                ['recurringOnDayOfWeek' => 'monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
                ['date_picker_every_day'],
            ],
            'days with times' => [
                ['days' => 'wednesday', 'time-from' => '13:00', 'time-to' => '17:00'],
                ['recurringOnDayOfWeek' => 'wednesday', 'recurringOnLocalTimeFrom' => 1300, 'recurringOnLocalTimeTo' => 1700],
                ['wednesday, 13:00 - 17:00'],
            ],
            'days with from after to' => [
                ['days' => 'wednesday', 'time-from' => '22:00', 'time-to' => '02:00'],
                ['recurringOnDayOfWeek' => 'wednesday'],
                ['wednesday'],
            ],
            'invalid times' => [
                ['days' => 'wednesday', 'time-from' => '25:00', 'time-to' => '9:30'],
                ['recurringOnDayOfWeek' => 'wednesday'],
                ['wednesday'],
            ],
            'times without days' => [
                ['time-from' => '13:00'],
                [],
                [],
            ],
        ];
    }

    private function createSearchForm(array $params): SearchForm
    {
        $annotation = new WidgetType();
        $annotation->id = 'search-form';

        $twigPreprocessor = $this->createMock(TwigPreprocessor::class);
        $twigPreprocessor->method('translateLabel')->willReturnArgument(0);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['search-form' => ['1' => $params]]));

        return new SearchForm(
            ['annotation' => $annotation],
            [
                'id' => '1',
                'settings' => [
                    'general' => [],
                    'header' => [],
                    'footer' => [],
                    'fields' => [
                        'time' => ['date_search' => ['enabled' => true, 'type' => 'date_picker']],
                    ],
                ],
            ],
            false,
            $this->createMock(\Twig_Environment::class),
            $twigPreprocessor,
            $this->createMock(RendererInterface::class),
            $requestStack,
            $this->createMock(RegionService::class)
        );
    }
}
