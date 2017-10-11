<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\AlterSearchResultsQueryInterface;
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
 *                      "label": "Extra",
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
     * Get the id of the targetted search results widget.
     */
    public function getTargettedSearchResultsWidgetId()
    {
        return $this->settings['search_results'] ?? '';
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
            if ($this->settings['filters']['when']) {
                $facets[] = $this->twigPreprocessor->getDateFacet();
            }
            if ($this->settings['filters']['what']) {
                $facets[] = $this->twigPreprocessor->preprocessFacet($facetsRaw->getFacetResults()['types'], 'type', 'nl');
            }
            if ($this->settings['filters']['where']) {
                $facets[] = $this->twigPreprocessor->preprocessFacet($facetsRaw->getFacetResults()['regions'], 'location', 'nl');
            }
            if ($this->settings['group_filters']['enabled']) {
                foreach ($this->settings['group_filters']['filters'] as $i => $filter) {
                    $facets[] = $this->twigPreprocessor->preprocessExtraFacet($filter, $i);
                }
            }
        }

        // Render twig with settings.
        return $this->twig->render(
            'widgets/facets-widget/facets-widget.html.twig',
            [
                'id' => $this->id,
                'facets' => $facets,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id, 'type' => 'facets', 'autoload' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function alterSearchResultsQuery(string $searchResultswidgetId, SearchQueryInterface $searchQuery)
    {
        // TODO: check search results ID hier.
        $this->buildQuery($searchQuery);
    }

    /**
     * Build the query object.
     */
    private function buildQuery(SearchQueryInterface $searchQuery)
    {
        // Add facets (if they haven't been added already).
        if ($this->settings['filters']['what']) {
            $existingFacets = array_filter(
                $searchQuery->getParameters(),
                function ($o) {
                    return $o instanceof Facet && $o->getValue() == 'types';
                }
            );
            if (empty($existingFacets)) {
                $searchQuery->addParameter(new Facet('types'));
            }
        }
        if ($this->settings['filters']['where']) {
            $existingFacets = array_filter(
                $searchQuery->getParameters(),
                function ($o) {
                    return $o instanceof Facet && $o->getValue() == 'regions';
                }
            );
            if (empty($existingFacets)) {
                $searchQuery->addParameter(new Facet('regions'));
            }
        }

        // Retrieve the current request query parameters using the global Application object and filter.
        $urlQueryParams = $this->filterUrlQueryParams($this->request->query->all());

        // Check if parameters require merging.
        if (count($urlQueryParams) > 1) {
            // Merge parameters per facet widget id.
            $urlQueryParams = array_merge_recursive(array_shift($urlQueryParams), $urlQueryParams['facets']);
        } else {
            // Go one level deeper.
            $urlQueryParams = array_shift($urlQueryParams);
        }

        // Get parameters for current facet if there are any.
        if (isset($urlQueryParams[$this->id])) {
            $urlQueryParams = $urlQueryParams[$this->id];
        } else {
            // Discard the parameters (will be added in the corresponding widget context).
            $urlQueryParams = [];
        }

        if (!empty($urlQueryParams)) {
            // Build advanced query string.
            $advancedQuery = [];

            // / Check for facets query params.
            if (isset($urlQueryParams['facet-location'])) {
                $advancedQuery[] = 'regions=' . $urlQueryParams['facet-location'];
                unset($urlQueryParams['facet-location']);
            }
            if (isset($urlQueryParams['facet-type'])) {
                $advancedQuery[] = 'terms.id:' . $urlQueryParams['facet-type'];
                unset($urlQueryParams['facet-type']);
            }
            if (isset($urlQueryParams['facet-date'])) {
                // Create ISO-8601 daterange from datetype.
                $dateRange = $this->convertDateTypeToDateRange($urlQueryParams['facet-date']);
                if (!empty($dateRange)) {
                    $advancedQuery[] = 'dateRange:' . $dateRange;
                }
                unset($urlQueryParams['facet-date']);
            }

            // Check for custom (extra) query params.
            if (isset($urlQueryParams['extra'])) {
                //
            }

            // Add advanced query string to API request.
            if (!empty($advancedQuery)) {
                $advancedQueryString = '';

                // Check for existing Query parameter.
                $existingQueries = array_filter(
                    $searchQuery->getParameters(),
                    function ($o) {
                        return $o instanceof Query;
                    }
                );

                if (!empty($existingQueries)) {
                    // Remove existing Query parameter.
                    $existingQuery = array_shift($existingQueries);
                    $searchQuery->removeParameter($existingQuery);
                    // Start new string with existing value.
                    $existingQueryString = $existingQuery->getValue();
                    $advancedQueryString = $existingQueryString . ' AND ';
                }

                // Add current facet parameters.
                $advancedQueryString .= implode(' AND ', $advancedQuery);

                $searchQuery->addParameter(
                    new Query($advancedQueryString)
                );
            }
        }
    }
}
