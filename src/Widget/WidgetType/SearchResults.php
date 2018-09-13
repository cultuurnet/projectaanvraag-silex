<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\Event\SearchResultsQueryAlter;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\Parameter\AudienceType;
use CultuurNet\SearchV3\Parameter\CalendarType;
use CultuurNet\SearchV3\Parameter\DateFrom;
use CultuurNet\SearchV3\Parameter\Id;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\Parameter\Facet;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use CultuurNet\SearchV3\ValueObjects\TranslatedString;
use Pimple\Container;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
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
 *              "body":"<p>Zelf een activiteit toevoegen? Dat kan via <a href='http://www.uitdatabank.be'>www.UiTdatabank.be</a></p>",
 *          },
 *          "items":{
 *              "type":{
 *                  "enabled":true
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
 *                  "enabled":true,
 *                  "label":"Wanneer"
 *              },
 *              "where":{
 *                  "enabled":true,
 *                  "label":"Waar"
 *              },
 *              "organizer":{
 *                  "enabled":false,
 *                  "label":"Organisatie"
 *              },
 *              "age":{
 *                  "enabled":true,
 *                  "label":"Leeftijd"
 *              },
 *              "audience":{
 *                  "enabled":false,
 *                  "label":"Toegang"
 *              },
 *              "language_icons":{
 *                  "enabled":false
 *              },
 *              "image":{
 *                  "enabled":true,
 *                  "width":480,
 *                  "height":360,
 *                  "default_image":true,
 *                  "position":"left"
 *              },
 *              "labels":{
 *                  "enabled":false,
 *                  "limit_labels":{
 *                      "enabled":false,
 *                  }
 *              },
 *              "facilities": {
 *                  "enabled":false,
 *                  "label": "Toegankelijkheid"
 *              },
 *              "read_more":{
 *                  "enabled":true,
 *                  "label":"Lees verder"
 *              },
 *          },
 *          "detail_page":{
 *              "map":false,
 *              "price_information":true,
 *              "contact_information":true,
 *              "reservation_information":true,
 *              "language_switcher":false,
 *              "uitpas_benefits":false,
 *              "share_buttons":true,
 *              "back_button":{
 *                  "enabled":true,
 *                  "label":"Agenda"
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
 *                  "enabled":true,
 *                  "label":"Wanneer"
 *              },
 *              "where":{
 *                  "enabled":true,
 *                  "label":"Waar"
 *              },
 *              "organizer":{
 *                  "enabled":false,
 *                  "label":"Organisatie"
 *              },
 *              "age":{
 *                  "enabled":true,
 *                  "label":"Leeftijd"
 *              },
 *              "audience":{
 *                  "enabled":false,
 *                  "label":"Toegang"
 *              },
 *              "language_icons":{
 *                  "enabled":false
 *              },
 *              "image":{
 *                  "enabled":true,
 *                  "width":480,
 *                  "height":360,
 *                  "default_image":true,
 *                  "position":"left"
 *              },
 *              "labels":{
 *                  "enabled":false,
 *                  "limit_labels":{
 *                      "enabled":false,
 *                  }
 *              },
 *              "facilities": {
 *                  "enabled":false,
 *                  "label": "Toegankelijkheid"
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
 *              "type":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_vlieg":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_uitpas":{
 *                  "enabled":"boolean"
 *              },
 *              "description":{
 *                  "enabled":"boolean",
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
 *              "organizer":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "age":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "audience":{
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
 *              "facilities":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "read_more":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              }
 *          },
 *          "search_params" : {
 *              "query":"string",
 *              "private": "boolean"
 *          },
 *          "detail_page":{
 *              "map":"boolean",
 *              "price_information":"boolean",
 *              "contact_information":"boolean",
 *              "reservation_information":"boolean",
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
 *              "organizer":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "age":{
 *                  "enabled":"boolean",
 *                  "label":"string"
 *              },
 *              "audience":{
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
 *              },
 *              "image":{
 *                  "enabled":"boolean",
 *                  "width":"integer",
 *                  "height":"integer",
 *                  "default_image":"boolean",
 *                  "position":"string"
 *              },
 *              "facilities":{
 *                  "enabled":"boolean",
 *                  "label":"string"
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
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var PagedCollection
     */
    protected $searchResult;

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
    public function __construct(array $pluginDefinition, array $configuration, bool $cleanup, \Twig_Environment $twig, TwigPreprocessor $twigPreprocessor, RendererInterface $renderer, SearchClient $searchClient, RequestStack $requestStack, MessageBusSupportingMiddleware $eventBus)
    {
        parent::__construct($pluginDefinition, $configuration, $cleanup, $twig, $twigPreprocessor, $renderer);
        $this->searchClient = $searchClient;
        $this->request = $requestStack->getCurrentRequest();
        $this->eventBus = $eventBus;
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
            $container['request_stack'],
            $container['event_bus']
        );
    }

    /**
     * Get the search result for current widget.
     */
    public function getSearchResult()
    {
        return $this->searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        // Retrieve the current request query parameters using the global Application object and filter.
        $urlQueryParams = $this->request->query->all();

        $query = new SearchQuery(true);

        // Pagination settings.
        $currentPageIndex = 0;
        // Limit items per page.
        $query->setLimit(self::ITEMS_PER_PAGE);

        $extraFilters = [];
        // Change query / defaults based on query string.
        if (isset($urlQueryParams['search-result']) && is_array($urlQueryParams['search-result']) && isset($urlQueryParams['search-result'][$this->index])) {
            $searchResultOptions = $urlQueryParams['search-result'][$this->index];
            // Check for page query param.
            if (isset($searchResultOptions['page'])) {
                // Set current page index.
                $currentPageIndex = $searchResultOptions['page'];
                // Move start according to the active page.
                $query->setStart($currentPageIndex * self::ITEMS_PER_PAGE);
            }

            if (!empty($searchResultOptions['hide-long-term'])) {
                $extraFilters['hide-long-term'] = true;
                $query->addParameter(new Query('!(calendarType:' . CalendarType::TYPE_PERIODIC . ')'));
            }

            if (!empty($searchResultOptions['hide-permanent'])) {
                $extraFilters['hide-permanent'] = true;
                $query->addParameter(new Query('!(calendarType:' . CalendarType::TYPE_PERMANENT . ')'));
            }
        }

        $private = false;
        // private
        if (!empty($this->settings['search_params']) && !empty($this->settings['search_params']['private'])) {
            $private = $this->settings['search_params']['private'];
        }

        // Build advanced query string
        $advancedQuery = [];

        // Read settings for search parameters from settings.
        if (!empty($this->settings['search_params']) && !empty($this->settings['search_params']['query'])) {
            // Convert comma-separated values to an advanced query string (Remove possible trailing comma).
            $advancedQuery[] = str_replace(',', ' AND ', '(' . rtrim($this->settings['search_params']['query'] . ')', ','));
        }

        if ($private) {
            $advancedQuery[] = '(audienceType:members OR audienceType:everyone)';
            $query->addParameter(new AudienceType('*'));
        }

        // Add advanced query string to API request.
        if (!empty($advancedQuery)) {
            $query->addParameter(
                new Query(
                    implode(' AND ', $advancedQuery)
                )
            );
        }

        // Sort by event end date.
        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        $activeFilters = [];
        $searchResultsQueryAlter = new SearchResultsQueryAlter($this->id, $query, $activeFilters);
        $this->eventBus->handle($searchResultsQueryAlter);

        // Retrieve results from Search API.
        $this->searchResult = $this->searchClient->searchEvents($query, $private);

        // Retrieve pager object.
        $pager = $this->retrievePagerData($this->searchResult->getItemsPerPage(), $this->searchResult->getTotalItems(), (int) $currentPageIndex);

        // Google tag manager wants to have some search terms in the tracked event.
        $searchedLocation = '';
        $searchedDate = '';
        $allActiveFilters = $searchResultsQueryAlter->getActiveFilters();
        $allActiveFilterNames = [];

        foreach ($allActiveFilters as $activeFilter) {
            if (strstr($activeFilter['name'], '[when]')) {
                $searchedDate = $activeFilter['label'];
            } elseif (strstr($activeFilter['name'], '[where]')) {
                $searchedLocation = $activeFilter['label'];
            }
            $allActiveFilterNames[] = $activeFilter['name'];
        }

        $mergedActiveFilters = [];
        $labels = [];
        $allActiveFilterNames = array_unique($allActiveFilterNames);

        // Merge active filters with same name
        foreach ($allActiveFilterNames as $activeFilterName) {
            foreach ($allActiveFilters as $activeFilter) {
                if ($activeFilterName == $activeFilter['name']) {
                    $labels[$activeFilterName][] = $activeFilter['label'];
                }
            }
        }

        foreach ($allActiveFilterNames as $activeFilterName) {
            $mergedActiveFilters[] = ['value'=>'','label' => join($labels[$activeFilterName], " en "),'is_default'=>false, 'name' => $activeFilterName];
        }

        $allActiveFilters = $mergedActiveFilters;

        $tagManagerData = [
            'pageTitleSuffix' => $searchedLocation . '|' . $searchedDate . '|' . $currentPageIndex,
            'search_query' => $query->__toString(),
        ];

        // Render twig with formatted results and item settings.
        return $this->twig->render(
            'widgets/search-results-widget/search-results-widget.html.twig',
            [
                'result_count' => $this->searchResult->getTotalItems(),
                'events' => $this->twigPreprocessor->preprocessEventList($this->searchResult->getMember()->getItems(), 'nl', $this->settings),
                'pager' => $pager,
                'settings_items' => $this->settings['items'],
                'settings_header' => $this->settings['header'],
                'settings_footer' => $this->settings['footer'],
                'settings_general' => $this->settings['general'],
                'id' => $this->index,
                'active_filters' => $allActiveFilters,
                'extra_filters' => $extraFilters,
                'tag_manager_data' => json_encode($tagManagerData),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        $this->renderer->attachJavascript(WWW_ROOT . '/assets/js/widgets/search-results/search-results.js');
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id, 'type' => 'search-results', 'autoload' => true]);
    }

    /**
     * Render the details for a requested item.
     */
    public function renderDetail()
    {

        if (!$this->request->query->has('cdbid')) {
            return '';
        }

        $query = new SearchQuery(true);
        $query->addParameter(new Id($this->request->query->get('cdbid')));
        // always perform full search, including offers for members
        $advancedQuery[] = '(audienceType:members OR audienceType:everyone)';
        $query->addParameter(
            new Query(
                implode(' AND ', $advancedQuery)
            )
        );
        $query->addParameter(new AudienceType('*'));
        $this->searchResult = $this->searchClient->searchEvents($query);

        $events = $this->searchResult->getMember()->getItems();
        if (count($events) === 0) {
            return '';
        }

        $langcode = $this->request->query->has('langcode') ? $this->request->query->get('langcode') : 'nl';
        $name = $events[0]->getName()->getValueForLanguage($langcode);
        $tagManagerData = [
            'pageTitleSuffix' => 'Event | ' . $name,
        ];

        // Render twig with formatted results and item settings.
        return $this->twig->render(
            'widgets/search-results-widget/detail-page.html.twig',
            [
                'event' => $this->twigPreprocessor->preprocessEventDetail($events[0], $langcode, $this->settings['detail_page']),
                'settings' => $this->settings['detail_page'],
                'tag_manager_data' => json_encode($tagManagerData),
            ]
        );
    }
}
