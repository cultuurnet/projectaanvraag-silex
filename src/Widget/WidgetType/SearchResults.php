<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use Pimple\Container;


/**
 * Provides the search form widget type.
 *
 * @WidgetType(
 *     id = "search-results",
 *      defaultSettings = {
 *          "general":{
 *              "current_search":true,
 *          },
 *          "header":{
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
 *              "current_search":"boolean"
 *          },
 *          "header":{
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
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * LayoutBase constructor.
     *
     * @param array $pluginDefinition
     * @param \Twig_Environment $twig
     * @param RendererInterface $renderer
     * @param array $configuration
     * @param bool $cleanup
     * @param SearchClient $searchClient
     */
    public function __construct(array $pluginDefinition, \Twig_Environment $twig, RendererInterface $renderer, array $configuration, bool $cleanup, SearchClient $searchClient)
    {
        parent::__construct($pluginDefinition, $twig, $renderer,$configuration, $cleanup);
        $this->searchClient = $searchClient;
    }

    /**
     * @inheritDoc
     */
    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $container['twig'],
            $container['widget_renderer'],
            $configuration,
            $cleanup,
            $container['search_api']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // Retrieve the current request query parameters using the global Application object and filter.
        global $app;
        $urlQueryParams = $this->filterUrlQueryParams($app['request_stack']->getCurrentRequest()->query->all());

        $query = new SearchQuery(true);

        // Pagination settings.
        $currentPageIndex = 0;
        // Limit items per page.
        // @todo: This should probably be a setting for the widget.
        $query->setLimit(20);
        // Check for page query param.
        if (isset($urlQueryParams['page'])) {
            // Set current page index.
            $currentPageIndex = $urlQueryParams['page'];
            // Move start according to the active page.
            $query->setStart($currentPageIndex * 20);
        }

        // Read settings for search parameters from settings.
        if ($this->settings['search_params']['query']) {
            // Convert comma-separated values to an advanced query string (Remove possible trailing comma).
            $query->addParameter(
                new Query(
                    str_replace(',', ' AND ',rtrim($this->settings['search_params']['query'], ','))
                )
            );
        }

        // Sort by event end date.
        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);

        // Retrieve pager object.
        $pager = $this->retrievePagerData($result->getItemsPerPage(), $result->getTotalItems(), (int)$currentPageIndex);

        // Render twig with formatted results and item settings.
        return $this->twig->render('widgets/search-results-widget/search-results-widget.html.twig', [
            'result_count' => $result->getTotalItems(),
            'events' => $this->formatEventData($result->getMember()->getItems(), 'nl'),
            'pager' => $pager,
            'settings' => $this->settings['items'],
            'header' => $this->settings['header']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
