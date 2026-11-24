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

class SearchFormGroupFilterTest extends TestCase
{
    /**
     * @dataProvider groupFilterProvider
     */
    public function testAlterSearchResultsQuery(array $selected, string $expectedQuery)
    {
        $searchResultsQueryAlter = new SearchResultsQueryAlter('1', new SearchQuery(), []);
        $this->createSearchForm($selected)->alterSearchResultsQuery($searchResultsQueryAlter);

        $query = null;
        foreach ($searchResultsQueryAlter->getSearchQuery()->getParameters() as $parameter) {
            if ($parameter->getKey() === 'q') {
                $query = $parameter->getValue();
            }
        }

        $this->assertSame($expectedQuery, $query);
    }

    public function groupFilterProvider(): array
    {
        return [
            'one option' => [[0 => '1'], '(b OR c)'],
            'multiple options of one filter' => [[0 => '0|1'], '((a) OR (b OR c))'],
            'options of different filters' => [[0 => '0', 1 => '0'], '(a) AND (d)'],
        ];
    }

    private function createSearchForm(array $selected): SearchForm
    {
        $annotation = new WidgetType();
        $annotation->id = 'search-form';

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['search-form' => ['1' => ['custom' => [3 => $selected]]]]));

        $filter = ['label' => '', 'placeholder' => '', 'type' => 'select_multiple', 'default_option' => ''];

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
                        'extra' => [
                            'group_filters' => [
                                'enabled' => true,
                                'filters' => [
                                    $filter + ['options' => [['label' => 'A', 'query' => 'a'], ['label' => 'B', 'query' => 'b OR c']]],
                                    $filter + ['options' => [['label' => 'D', 'query' => 'd']]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            false,
            $this->createMock(\Twig_Environment::class),
            $this->createMock(TwigPreprocessor::class),
            $this->createMock(RendererInterface::class),
            $requestStack,
            $this->createMock(RegionService::class)
        );
    }
}
