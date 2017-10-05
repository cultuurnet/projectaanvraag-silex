<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\Parameter\Facet;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

use Pimple\Container;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the search form widget type.
 *
 * @WidgetType(
 *     id = "search-results",
 *      defaultSettings = {
 *          "general":{
 *              "current_search":true,
 *              "exclude": {
 *                  "long_term":"true",
 *                  "permanent":"true"
 *              }
 *          },
 *          "header":{
 *              "body":"",
 *          },
 *          "footer":{
 *              "body":"",
 *          },
 *          "items":{
 *              "icon_vlieg":{
 *                  "enabled":true
 *              },
 *              "icon_uitpas":{
 *                  "enabled":true
 *              },
 *              "description":{
 *                  "enabled":true,
 *                  "characters":200
 *              },
 *              "when":{
 *                  "enabled":false,
 *                  "label":"Wanneer"
 *              },
 *              "where":{
 *                  "enabled":true,
 *                  "label":"Waar"
 *              },
 *              "age":{
 *                  "enabled":true,
 *                  "label":"Leeftijd"
 *              },
 *              "language_icons":{
 *                  "enabled":false
 *              },
 *              "image":{
 *                  "enabled":true,
 *                  "width":100,
 *                  "height":80,
 *                  "default_image":true,
 *                  "position":"left"
 *              },
 *              "labels":{
 *                  "enabled":false,
 *                  "limit_labels":{
 *                      "enabled":false,
 *                  }
 *              },
 *              "read_more":{
 *                  "enabled":true,
 *                  "label":"Lees verder"
 *              },
 *          },
 *          "detail_page":{
 *              "price_information":true,
 *              "share_buttons":true,
 *              "back_button":{
 *                  "enabled":true,
 *                  "label":"Volledig aanbod"
 *              },
 *              "icon_vlieg":{
 *                  "enabled":true
 *              },
 *              "icon_uitpas":{
 *                  "enabled":true
 *              },
 *              "description":{
 *                  "enabled":true,
 *                  "characters":200,
 *                  "label":"",
 *              },
 *              "when":{
 *                  "enabled":false,
 *                  "label":"Wanneer"
 *              },
 *              "where":{
 *                  "enabled":true,
 *                  "label":"Waar"
 *              },
 *              "age":{
 *                  "enabled":true,
 *                  "label":"Leeftijd"
 *              },
 *              "language_icons":{
 *                  "enabled":false
 *              },
 *              "image":{
 *                  "enabled":true,
 *                  "width":300,
 *                  "height":200,
 *                  "default_image":true,
 *                  "position":"left"
 *              },
 *              "labels":{
 *                  "enabled":false,
 *                  "limit_labels":{
 *                      "enabled":false,
 *                  }
 *              }
 *          }
 *      },
 *      allowedSettings = {
 *          "general":{
 *              "current_search":"boolean",
 *              "exclude": {
 *                  "long_term":"boolean",
 *                  "permanent":"boolean"
 *              }
 *          },
 *          "header":{
 *              "body":"string"
 *          },
 *          "footer":{
 *              "body":"string"
 *          },
 *          "items":{
 *              "icon_vlieg":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_uitpas":{
 *                  "enabled":"boolean"
 *              },
 *              "description":{
 *                  "enabled":"boolean",
 *                  "label":"string",
 *                  "characters":"integer"
 *              },
 *              "when":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "where":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "age":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "language_icons":{
 *                  "enabled":"boolean"
 *              },
 *              "image":{
 *                  "enabled":"boolean",
 *                  "width":"integer",
 *                  "height":"integer",
 *                  "default_image":"boolean",
 *                  "position":"string"
 *              },
 *              "labels":{
 *                  "enabled":"boolean",
 *                  "limit_labels":{
 *                      "enabled":"boolean",
 *                      "labels":"string"
 *                  }
 *              },
 *              "read_more":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              }
 *          },
 *          "search_params" : {
 *              "query":"string"
 *          },
 *          "detail_page":{
 *              "map":"boolean",
 *              "price_information":"boolean",
 *              "language_switcher":"boolean",
 *              "uitpas_benefits":"boolean",
 *              "share_buttons":"boolean",
 *              "back_button":{
 *                  "enabled":"boolean",
 *                  "label":"string",
 *                  "url":"string"
 *              },
 *              "icon_vlieg":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_uitpas":{
 *                  "enabled":"boolean"
 *              },
 *              "when":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "where":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "age":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "language_icons":{
 *                  "enabled":"boolean"
 *              },
 *              "labels":{
 *                  "enabled":"boolean",
 *                  "limit_labels":{
 *                      "enabled":"boolean",
 *                      "labels":"string"
 *                  }
 *              }
 *          }
 *     }
 * )
 */
class SearchResults extends WidgetTypeBase
{

    /**
     * Items per pager is currently fixed.
     */
    const ITEMS_PER_PAGE = 10;

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * @var RequestStack
     */
    protected $request;

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
     * {@inheritdoc}
     */
    public function render()
    {
        // Retrieve the current request query parameters using the global Application object and filter.
        $urlQueryParams = $this->filterUrlQueryParams($this->request->query->all());

        $query = new SearchQuery(true);

        // Pagination settings.
        $currentPageIndex = 0;
        // Limit items per page.
        $query->setLimit(self::ITEMS_PER_PAGE);

        // Check for page query param.
        if (isset($urlQueryParams['page'])) {
            // Set current page index.
            $currentPageIndex = $urlQueryParams['page'];
            // Move start according to the active page.
            $query->setStart($currentPageIndex * self::ITEMS_PER_PAGE);
        }

        // Add facets (datetime is missing from v3?).
        //$query->addParameter(new Facet('regions'));
        //$query->addParameter(new Facet('types'));
        //$query->addParameter(new Facet('themes'));
        //$query->addParameter(new Facet('facilities'));


        // Build advanced query string
        $advancedQuery = [];

        // Read settings for search parameters from settings.
        /*
        if (!empty($this->settings['search_params']) && !empty($this->settings['search_params']['query'])) {
            // Convert comma-separated values to an advanced query string (Remove possible trailing comma).
            $advancedQuery[] = str_replace(',', ' AND ', rtrim($this->settings['search_params']['query'], ','));
        }
        */

        // / Check for facets query params.
        if (isset($urlQueryParams['region'])) {
            $advancedQuery[] = 'regions=' . $urlQueryParams['region'];
        }
        if (isset($urlQueryParams['type'])) {
            $advancedQuery[] = 'terms.id:' . $urlQueryParams['type'];
        }
        if (isset($urlQueryParams['date'])) {
            // Create ISO-8601 daterange from datetype.
            $dateRange = $this->convertDateTypeToDateRange($urlQueryParams['date']);
            if (!empty($dateRange)) {
                $advancedQuery[] = 'dateRange:' . $dateRange;
            }
        }

        // Add adanced query string to API request.
        if (!empty($advancedQuery)) {
            $query->addParameter(
                new Query(
                    implode('AND', $advancedQuery)
                )
            );
        }

        // Sort by event end date.
        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);

        // Retrieve pager object.
        $pager = $this->retrievePagerData($result->getItemsPerPage(), $result->getTotalItems(), (int) $currentPageIndex);

        if (!isset($this->settings['items']['description']['label'])) {
            $this->settings['items']['description']['label'] = '';
        }

        // Render twig with formatted results and item settings.
        return $this->twig->render(
            'widgets/search-results-widget/search-results-widget.html.twig',
            [
                'result_count' => $result->getTotalItems(),
                'events' => $this->twigPreprocessor->preprocessEventList($result->getMember()->getItems(), 'nl', $this->settings),
                'pager' => $pager,
                'settings_items' => $this->settings['items'],
                'settings_header' => $this->settings['header'],
                'settings_footer' => $this->settings['footer'],
                'settings_general' => $this->settings['general'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
