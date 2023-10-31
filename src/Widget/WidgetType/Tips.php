<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;
use CultuurNet\SearchV3\Parameter\AddressCountry;
use CultuurNet\SearchV3\Parameter\AudienceType;
use CultuurNet\SearchV3\Parameter\AvailableFrom;
use CultuurNet\SearchV3\Parameter\AvailableTo;
use CultuurNet\SearchV3\Parameter\CalendarSummary;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\SearchV3\ValueObjects\CalendarSummaryFormat;

use Pimple\Container;

/**
 * Provides the tips widget type.
 *
 * @WidgetType(
 *      id = "tips",
 *      defaultSettings = {
 *          "general":{
 *              "items":3,
 *              "detail_link":{
 *                  "enabled":false,
 *                  "cdbid":"query_string"
 *              },
 *              "labels_as_icons":{
 *                  "enabled":false
 *              }
 *          },
 *          "items":{
 *              "type":{
 *                  "enabled":true
 *              },
 *              "theme":{
 *                  "enabled":true
 *              },
 *              "icon_vlieg":{
 *                  "enabled":true
 *              },
 *              "icon_uitpas":{
 *                  "enabled":true
 *              },
 *              "icon_museumpass":{
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
 *              "age":{
 *                  "enabled":false,
 *                  "label":"Leeftijd"
 *              },
 *              "audience":{
 *                  "enabled":false,
 *                  "label":"Toegang"
 *              },
 *              "language_icons":{
 *                  "enabled":false
 *              },
 *               "organizer":{
 *                  "enabled":false,
 *                  "label":"Organisatie"
 *              },
 *              "image":{
 *                  "enabled":true,
 *                  "width":480,
 *                  "height":360,
 *                  "default_image": {
 *                      "enabled":true,
 *                      "type":"uit"
 *                  },
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
 *              "price_information":{
 *                  "enabled":false
 *              },
 *              "reservation_information":{
 *                  "enabled":false
 *              }
 *          },
 *          "search_params" : {
 *              "country": "BE",
 *          }
 *      },
 *      allowedSettings = {
 *          "general":{
 *              "items":"integer",
 *              "detail_link":{
 *                  "enabled":"boolean",
 *                  "url":"string",
 *                  "cdbid":"string"
 *              },
 *              "labels_as_icons":{
 *                  "enabled":"boolean"
 *              }
 *          },
 *          "items":{
 *              "type":{
 *                  "enabled":"boolean"
 *              },
 *              "theme":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_vlieg":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_uitpas":{
 *                  "enabled":"boolean"
 *              },
 *              "icon_museumpass":{
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
 *                  "default_image": {
 *                      "enabled":"boolean",
 *                      "type":"string"
 *                  },
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
 *              },
 *              "price_information":{
 *                  "enabled":"boolean"
 *              },
 *              "reservation_information":{
 *                  "enabled":"boolean"
 *              }
 *          },
 *          "search_params" : {
 *              "query": "string",
 *              "private": "boolean",
 *              "country": "string",
 *          }
 *      }
 * )
 */
final class Tips extends WidgetTypeBase
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * Tips constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     * @param array $pluginDefinition
     * @param array $configuration
     * @param bool $cleanup
     * @param \Twig_Environment $twig
     * @param TwigPreprocessor $twigPreprocessor
     * @param RendererInterface $renderer
     * @param SearchClient $searchClient
     */
    public function __construct(array $pluginDefinition, array $configuration, bool $cleanup, \Twig_Environment $twig, TwigPreprocessor $twigPreprocessor, RendererInterface $renderer, SearchClient $searchClient)
    {
        parent::__construct($pluginDefinition, $configuration, $cleanup, $twig, $twigPreprocessor, $renderer);
        $this->searchClient = $searchClient;
    }

    public static function create(Container $container, array $pluginDefinition, array $configuration, bool $cleanup)
    {
        return new static(
            $pluginDefinition,
            $configuration,
            $cleanup,
            $container['twig'],
            $container['widget_twig_preprocessor'],
            $container['widget_renderer'],
            $container['search_api']
        );
    }

    public function render($cdbid = '', $preferredLanguage = 'nl')
    {
        $query = new SearchQuery(true);
        $boostQuery = false;

        if ($cdbid == '') {
            // Read settings for search parameters and limit.
            if ($this->settings['general']['items']) {
                // Set limit.
                $query->setLimit($this->settings['general']['items']);
            }

            if (!empty($this->settings['search_params'])) {
                if (!empty($this->settings['search_params']['query'])) {
                    if (strpos($this->settings['search_params']['query'], '^') !== false) {
                        $boostQuery = true;
                    }
                    // Convert comma-separated values to an advanced query string (Remove possible trailing comma).
                    $query->addParameter(
                        new Query(
                            str_replace(',', ' AND ', rtrim($this->settings['search_params']['query'], ','))
                        )
                    );
                }

                if (!empty($this->settings['search_params']['private']) &&
                    $this->settings['search_params']['private']) {
                    $query->addParameter(new Query('(audienceType:members OR audienceType:everyone)'));
                    $query->addParameter(new AudienceType('*'));
                }
            }

            // Sort by score when query contains boosting elements.
            if ($boostQuery) {
                $query->addSort('score', SearchQueryInterface::SORT_DIRECTION_DESC);
            }
            // Sort by event end date.
            $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);
        } else {
            $cdbids = explode(' ', $cdbid);
            $query->addParameter(new Query('id:' . implode(' OR id:', $cdbids)));
            // Disable default filters, except workflowstatus
            $query->addParameter(new AudienceType('*'));
            $query->addParameter(AvailableTo::wildcard());
            $query->addParameter(AvailableFrom::wildcard());
        }

        $addressCountry = !empty($this->settings['search_params']['country']) ? $this->settings['search_params']['country']: 'BE';

        if ($addressCountry !== '') {
            $query->addParameter(new AddressCountry($addressCountry));
        }

        $query->addParameter(new CalendarSummary(new CalendarSummaryFormat('text', 'sm')));
        $query->addParameter(new CalendarSummary(new CalendarSummaryFormat('text', 'md')));
        $query->addParameter(new CalendarSummary(new CalendarSummaryFormat('text', 'lg')));

        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);

        // Render twig with formatted results and item settings.
        return $this->twig->render(
            'widgets/tips-widget/tips-widget.html.twig',
            [
                'events' => $this->twigPreprocessor->preprocessEventList($result->getMember()->getItems(), $preferredLanguage, $this->settings),
                'settings_items' => $this->settings['items'],
                'settings_general' => $this->settings['general'],
                'preferredLanguage' => $preferredLanguage,
            ]
        );
    }

    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id, 'type' => 'tips', 'autoload' => true]);
    }
}
