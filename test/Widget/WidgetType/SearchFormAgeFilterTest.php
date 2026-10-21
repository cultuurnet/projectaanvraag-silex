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

class SearchFormAgeFilterTest extends TestCase
{
    /**
     * Test if the selected ages or birth years are added to the search query.
     *
     * @dataProvider ageFilterProvider
     */
    public function testAlterSearchResultsQuery(array $params, ?string $expectedQuery, array $expectedLabels)
    {
        $searchResultsQueryAlter = new SearchResultsQueryAlter('1', new SearchQuery(), []);
        $this->createSearchForm($params)->alterSearchResultsQuery($searchResultsQueryAlter);

        $query = null;
        foreach ($searchResultsQueryAlter->getSearchQuery()->getParameters() as $parameter) {
            if ($parameter->getKey() === 'q') {
                $query = $parameter->getValue();
            }
        }

        $this->assertSame($expectedQuery, $query);
        $this->assertSame($expectedLabels, array_column($searchResultsQueryAlter->getActiveFilters(), 'label'));
    }

    public function ageFilterProvider(): array
    {
        $year = (int) date('Y');

        return [
            'ages' => [
                ['age' => '5|4|5'],
                '(typicalAgeRange:[4 TO 4] OR typicalAgeRange:[5 TO 5])',
                ['4 jaar, 5 jaar'],
            ],
            'birth years' => [
                ['birth-year' => ($year - 4) . '|' . ($year - 5)],
                '(birthdateRange:[' . ($year - 5) . '-01-01 TO ' . ($year - 5) . '-12-31] OR birthdateRange:[' . ($year - 4) . '-01-01 TO ' . ($year - 4) . '-12-31])',
                [($year - 5) . ', ' . ($year - 4)],
            ],
            'values outside the 2-12 range' => [
                ['age' => '1|13|foo', 'birth-year' => ($year - 1) . '|' . ($year - 13)],
                null,
                [],
            ],
        ];
    }

    private function createSearchForm(array $params): SearchForm
    {
        $annotation = new WidgetType();
        $annotation->id = 'search-form';

        $twigPreprocessor = $this->createMock(TwigPreprocessor::class);
        $twigPreprocessor->method('translateLabel')->willReturnCallback(
            function (string $label, string $type, string $language, array $parameters) {
                return $parameters['%age%'] . ' jaar';
            }
        );

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
                        'time' => ['date_search' => ['enabled' => false]],
                        'age' => ['age_filter' => ['enabled' => true]],
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
