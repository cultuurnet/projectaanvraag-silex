<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\AlterSearchResultsQueryInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\SearchResultsQueryAlter;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\Parameter\Facet;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\SearchV3\ValueObjects\FacetResult;
use CultuurNet\SearchV3\ValueObjects\FacetResultItem;
use CultuurNet\SearchV3\ValueObjects\FacetResults;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use Pimple\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

/**
 * Provides the facets widget type.
 *
 * @WidgetType(
 *      id = "facets",
 *      defaultSettings = {
 *          "filters" :{
 *              "what":true,
 *              "where":true,
 *              "when":false,
 *          },
 *          "group_filters" :{
 *              "enabled":false,
 *              "filters": {
 *                  {
 *                      "label": "Extra opties",
 *                      "type": "link",
 *                      "placeholder": "",
 *                      "options": {
 *                          {
 *                              "label": "Voor UiTPAS en Paspartoe",
 *                              "query": "labels:uitpas* OR labels:paspartoe"
 *                          },
 *                          {
 *                              "label": "Voor kinderen",
 *                              "query": "typicalAgeRange:12 OR labels:""ook voor kinderen"""
 *                          },
 *                          {
 *                              "label": "Gratis activiteiten",
 *                              "query": "price:0.0"
 *                          }
 *                      }
 *                  }
 *              }
 *          }
 *      },
 *      allowedSettings = {
 *          "search_results":"string",
 *          "filters":{
 *              "what":"boolean",
 *              "where":"boolean",
 *              "when":"boolean"
 *          },
 *          "group_filters":"CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *      }
 * )
 */
class Facets extends WidgetTypeBase implements AlterSearchResultsQueryInterface
{

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * @var PagedCollection $searchResult
     */
    private $searchResult;

    /**
     * SearchResults constructor.
     *
     * @param array $pluginDefinition
     * @param array $configuration
     * @param bool $cleanup
     * @param \Twig_Environment $twig
     * @param TwigPreprocessor $twigPreprocessor
     * @param RendererInterface $renderer
     * @param SearchClient $searchClient
     */
    public function __construct(array $pluginDefinition, array $configuration, bool $cleanup, \Twig_Environment $twig, TwigPreprocessor $twigPreprocessor, RendererInterface $renderer, SearchClient $searchClient, RequestStack $requestStack)
    {
        parent::__construct($pluginDefinition, $configuration, $cleanup, $twig, $twigPreprocessor, $renderer);
        $this->searchClient = $searchClient;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $configuration,
            $cleanup,
            $container['twig'],
            $container['widget_twig_preprocessor'],
            $container['widget_renderer'],
            $container['search_api'],
            $container['request_stack']
        );
    }

    /**
     * Get the id of the targeted search results widget.
     */
    public function getTargetedSearchResultsWidgetId()
    {
        return $this->settings['search_results'] ?? '';
    }

    /**
     * Set the id of the targeted search results widget.
     *
     * @param string $targetId
     */
    public function setTargettedSearchResultsWidgetId($targetId)
    {
        $this->settings['search_results'] = $targetId;
    }

    /**
     * Set the search result for current facet.
     * @param PagedCollection $searchResult
     */
    public function setSearchResult(PagedCollection $searchResult)
    {
        $this->searchResult = $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // If a render is requested without search results context, perform a full search.
        if (empty($this->searchResult)) {
            $query = new SearchQuery(true);

            // Limit items per page.
            $query->setLimit(1);
            $this->buildQuery($query);
            $this->searchResult = $this->searchClient->searchEvents($query);
        }

        // Preprocess facets before sending to template.
        $facets = [];
        $facetsRaw = $this->searchResult->getFacets();
        if ($facetsRaw) {
            // Retrieve current url parameters (for checking active options).
            $urlQueryParams = $this->getFacetParameters();

            if ($this->settings['filters']['when']) {
                $activeValue = $urlQueryParams['when'] ?? '';
                $facets[] = $this->twigPreprocessor->getDateFacet($activeValue);
            }

            if ($this->settings['filters']['where']) {
                $activeValue = $urlQueryParams['where'] ?? '';
                $facets[] = $this->twigPreprocessor->preprocessFacet($facetsRaw->getFacetResults()['regions'], 'where', 'Waar', $activeValue);
            }

            if ($this->settings['filters']['what']) {
                $activeValue = $urlQueryParams['what'] ?? '';
                $facets[] = $this->twigPreprocessor->preprocessFacet($facetsRaw->getFacetResults()['types'], 'what', 'Wat', $activeValue);
            }

            if ($this->settings['group_filters']['enabled']) {
                foreach ($this->settings['group_filters']['filters'] as $i => $filter) {
                    $activeValue = $urlQueryParams['custom'][$i] ?? [];
                    $facets[] = $this->twigPreprocessor->preprocessCustomFacet($filter, $i, $activeValue);
                }
            }
        }

        // Render twig with settings.
        return $this->twig->render(
            'widgets/facets-widget/facets-widget.html.twig',
            [
                'id' => $this->index, // Use the index as identifier for smaller querystrings.
                'facets' => $facets,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        $this->renderer->attachJavascript(WWW_ROOT . '/assets/js/widgets/facets/facets.js');
        return $this->twig->render(
            'widgets/facets-widget/facet-placeholder.html.twig',
            [
                'id' => $this->id,
                'type' => 'facets',
                'facet_target_id' => $this->getTargetedSearchResultsWidgetId(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function alterSearchResultsQuery(SearchResultsQueryAlter $searchResultsQueryAlter)
    {
        if ($this->getTargetedSearchResultsWidgetId() == $searchResultsQueryAlter->getWidgetId()) {
            $this->buildQuery($searchResultsQueryAlter->getSearchQuery(), $searchResultsQueryAlter);
        }
    }

    /**
     * Build the query object.
     */
    private function buildQuery(SearchQueryInterface $searchQuery, SearchResultsQueryAlter $searchResultsQueryAlter = null)
    {

        // Check what facets are already added.
        $existingFacets = [];
        foreach ($searchQuery->getParameters() as $parameter) {
            if ($parameter instanceof Facet) {
                $existingFacets[] = $parameter->getValue();
            }
        }

        // Add facets (if they haven't been added already).
        if ($this->settings['filters']['what']) {
            if (!in_array('types', $existingFacets)) {
                $searchQuery->addParameter(new Facet('types'));
            }
        }
        if ($this->settings['filters']['where']) {
            if (!in_array('regions', $existingFacets)) {
                $searchQuery->addParameter(new Facet('regions'));
            }
        }

        // Build advanced query string.
        $advancedQuery = [];
        $searchResultsActiveFilters = [];
        if ($searchResultsQueryAlter) {
            $searchResultsActiveFilters = $searchResultsQueryAlter->getActiveFilters();
        }

        // Retrieve filtered parameters and add them to the query.
        $enabledFacetOptions = $this->getEnabledFacetOptions();
        foreach ($enabledFacetOptions as $key => $value) {
            if (is_array($value)) {
                switch ($key) {
                    case 'what':
                        $advancedQuery[] = 'terms.id:' . key($value);

                        $searchResultsActiveFilters[] = [
                            'label' => current($value),
                            'name' => 'facets[' . $this->index . '][what][' . key($value) . ']',
                            'is_default' => false,
                        ];
                        break;

                    case 'where':
                        $advancedQuery[] = 'regions:' . key($value);

                        $searchResultsActiveFilters[] = [
                            'label' => current($value),
                            'name' => 'facets[' . $this->index . '][when][' . key($value) . ']',
                            'is_default' => false,
                        ];

                        break;

                    case 'when':
                        // Create ISO-8601 daterange from datetype.
                        $dateRange = $this->convertDateTypeToDateRange(key($value));
                        if (!empty($dateRange)) {
                            $advancedQuery[] = 'dateRange:' . $dateRange['query'];

                            $searchResultsActiveFilters[] = [
                                'label' => current($value),
                                'name' => 'facets[' . $this->index . '][when][' . key($value) . ']',
                                'is_default' => false,
                            ];
                        }
                        break;

                    case 'custom':
                        // Check for custom (extra) query params and retrieve options from settings.
                        $extraFilters = $this->settings['group_filters']['filters'];
                        foreach ($value as $groupKey => $extraGroup) {
                            if (isset($extraFilters[$groupKey])) {
                                $options = $extraFilters[$groupKey]['options'];
                                foreach ($extraGroup as $key => $extra) {
                                    $advancedQuery[] = '(' . $options[$key]['query'] . ')';

                                    $searchResultsActiveFilters[] = [
                                        'label' => $options[$key]['label'],
                                        'name' => 'facets[' . $this->index . '][custom][' . $groupKey . '][' . $key . ']',
                                        'is_default' => false,
                                    ];
                                }
                            }
                        }

                        break;
                }
            }
        }

        // Add advanced query string to API request.
        if (!empty($advancedQuery)) {
            $searchQuery->addParameter(
                new Query(implode($advancedQuery, ' AND '))
            );

            if ($searchResultsQueryAlter) {
                $searchResultsQueryAlter->setActiveFilters($searchResultsActiveFilters);
            }
        }
    }

    /**
     * Get all enabled facet options for current facets widget.
     */
    private function getEnabledFacetOptions()
    {

        $facetParameters = $this->getFacetParameters();
        $activeOptions = [];

        if ($this->settings['filters']['when'] && isset($facetParameters['when'])) {
            $activeOptions['when'] = $facetParameters['when'];
        }
        if ($this->settings['filters']['what'] && isset($facetParameters['what'])) {
            $activeOptions['what'] = $facetParameters['what'];
        }
        if ($this->settings['filters']['where'] && isset($facetParameters['where'])) {
            $activeOptions['where'] = $facetParameters['where'];
        }

        // For group filter, check per filter what options are active.
        if ($this->settings['group_filters']['enabled']) {
            foreach ($this->settings['group_filters']['filters'] as $i => $filter) {
                if (isset($facetParameters['custom'][$i])) {
                    foreach ($facetParameters['custom'][$i] as $activeOption => $indication) {
                        $activeOptions['custom'][$i][$activeOption] = true;
                    }
                }
            }
        }

        return $activeOptions;
    }

    /**
     * Retrieve the current facet query parameters to use.
     *
     * @return array|mixed
     */
    private function getFacetParameters()
    {
        // Retrieve all the query parameters for facets.
        $facetQueryParams = $this->request->query->get('facets');

        // Get parameters for current facet if there are any.
        if (isset($facetQueryParams[$this->index])) {
            return $facetQueryParams[$this->index];
        }

        return [];
    }
}
