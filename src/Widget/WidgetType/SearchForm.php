<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\AlterSearchResultsQueryInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\SearchResultsQueryAlter;
use CultuurNet\ProjectAanvraag\Widget\RegionService;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\Twig\TwigPreprocessor;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use CultuurNet\SearchV3\Parameter\Query;
use Pimple\Container;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the search form widget type.
 *
 * @WidgetType(
 *      id = "search-form",
 *      defaultSettings = {
 *          "general": {
 *              "destination": "",
 *              "new_window": false,
 *              "button_label": "Zoeken"
 *          },
 *          "header": {
 *              "body": "<h2><img border='0' height='35px' class='logo-uiv' src='{{ base_url }}/assets/images/uit-logo.svg' alt='Uit in' /> in ...</h2>",
 *          },
 *          "fields": {
 *              "type": {
 *                  "keyword_search": {
 *                      "enabled" : true,
 *                      "label": "Wat",
 *                      "placeholder": "Bv. concert, Bart Peeters,...",
 *                  },
 *                  "group_filters": {
 *                      "enabled": false,
 *                  }
 *              },
 *              "location": {
 *                  "keyword_search": {
 *                      "enabled" : true,
 *                      "label": "Waar",
 *                      "placeholder": "Bv. Gent, Provincie Limburg",
 *                  },
 *                  "group_filters": {
 *                      "enabled": false
 *                  }
 *              },
 *              "time": {
 *                  "date_search": {
 *                      "enabled" : true,
 *                      "label": "Wanneer",
 *                      "placeholder": "Kies een periode",
 *                      "options": {
 *                          "today": true,
 *                          "tomorrow": true,
 *                          "weekend": true,
 *                          "days_7": true,
 *                          "days_14": true,
 *                          "days_30": true,
 *                          "custom_date": true
 *                      },
 *                      "default_option": "placeholder"
 *                  },
 *                  "group_filters": {
 *                      "enabled": false
 *                  }
 *              },
 *              "extra": {
 *                  "group_filters": {
 *                      "enabled": false
 *                  },
 *              },
 *              "facility_filters": {
 *                  "enabled": false,
 *                  "filters" : {
 *                      {
 *                          "label" : "Algemene voorzieningen",
 *                          "placeholder" : "Andere voorzieningen",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                  "label" : "Alternatieve ingang",
 *                                  "query" : "terms.id:3.28.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Assistentie",
 *                                  "query" : "terms.id:3.23.2.0.0"
 *                              },
 *                              {
 *                                  "label" : "Begeleiderspas",
 *                                  "query" : "terms.id:3.39.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Gegarandeerd zicht",
 *                                  "query" : "terms.id:3.29.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Inter-assistentie",
 *                                  "query" : "terms.id:3.38.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Inter-toegankelijkheidsgarantie",
 *                                  "query" : "terms.id:3.40.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Voorbehouden camping",
 *                                  "query" : "terms.id:3.32.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Contactpunt voor personen met handicap",
 *                                  "query" : "terms.id:3.25.0.0.0"
 *                              }
 *                          }
 *                      },
 *                      {
 *                          "label" : "Motorische handicap",
 *                          "placeholder" : "Motorische handicap",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                  "label" : "Stroomvoorziening rolstoelen",
 *                                  "query" : "terms.id:3.37.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Rolstoelpodium",
 *                                  "query" : "terms.id:3.30.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Parkeerplaats",
 *                                  "query" : "terms.id:3.26.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Rolstoeltoegankelijk",
 *                                  "query" : "terms.id:3.27.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Toegankelijk sanitair",
 *                                  "query" : "terms.id:3.31.0.0.0"
 *                              },
 *                              {
 *                                  "label" : "Rolstoel ter beschikking",
 *                                  "query" : "terms.id:3.23.3.0.0"
 *                              },
 *                              {
 *                                  "label" : "Verzorgingsruimte",
 *                                  "query" : "terms.id:3.36.0.0.0"
 *                              }
 *                          }
 *                      },
 *                      {
 *                          "label" : "Blinden en slechtzienden",
 *                          "placeholder" : "Blinden en slechtzienden",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                  "label" : "Audiodescriptie",
 *                                  "query" : "terms.id:3.13.2.0.0"
 *                              },
 *                              {
 *                                  "label" : "Voorzieningen voor assistentiehonden",
 *                                  "query" : "terms.id:3.13.1.0.0"
 *                              }
 *                          }
 *                      },
 *                      {
 *                          "label" : "Doven en slechthorenden",
 *                          "placeholder" : "Doven en slechthorenden",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                  "label" : "Boven- of ondertiteling",
 *                                  "query" : "terms.id:3.17.3.0.0"
 *                              },
 *                              {
 *                                  "label" : "Ringleiding",
 *                                  "query" : "terms.id:3.17.1.0.0"
 *                              },
 *                              {
 *                                  "label" : "Tolken Vlaamse Gebarentaal",
 *                                  "query" : "terms.id:3.33.0.0.0"
 *                              }
 *                          }
 *                      },
 *                      {
 *                          "label" : "Verstandelijke beperking",
 *                          "placeholder" : "Verstandelijke beperking",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                  "label" : "Vereenvoudigde informatie",
 *                                  "query" : "terms.id:3.34.0.0.0"
 *                              }
 *                          }
 *                      },
 *                      {
 *                          "label" : "Neurodiverse personen",
 *                          "placeholder" : "Neurodiverse personen",
 *                          "type" : "select_multiple",
 *                          "default_option" : "",
 *                          "options" : {
 *                              {
 *                                   "label" : "Prikkelarm aanbod",
 *                                   "query" : "terms.id:H28fcfRKFQAQs00K9NF9hh"
 *                              },
 *                              {
 *                                   "label" : "Afspraken en voorspelbaarheid",
 *                                   "query" : "terms.id:4Vz9eZf0cnQmtfqcGGnNMF"
 *                              }
 *                          }
 *                      }
 *                  }
 *              }
 *          },
 *          "footer": {
 *              "body": ""
 *          }
 *      },
 *      allowedSettings = {
 *          "general": {
 *              "destination": "string",
 *              "new_window": "boolean",
 *              "button_label": "string"
 *          },
 *          "header": {
 *              "body": "string",
 *          },
 *          "fields": {
 *              "type": {
 *                  "keyword_search": {
 *                      "enabled" : "boolean",
 *                      "label": "string",
 *                      "placeholder": "string",
 *                  },
 *                  "group_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *              },
 *              "location": {
 *                  "keyword_search": {
 *                      "enabled" : "boolean",
 *                      "label": "string",
 *                      "placeholder": "string",
 *                  },
 *                  "group_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *              },
 *              "time": {
 *                  "date_search": {
 *                      "enabled" : "boolean",
 *                      "label": "string",
 *                      "placeholder": "string",
 *                      "options": {
 *                          "today": "boolean",
 *                          "tomorrow": "boolean",
 *                          "weekend": "boolean",
 *                          "days_7": "boolean",
 *                          "days_14": "boolean",
 *                          "days_30": "boolean",
 *                          "custom_date": "boolean"
 *                      },
 *                      "default_option": "string"
 *                  },
 *                  "group_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *              },
 *              "extra": {
 *                  "group_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *              },
 *              "facility_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *          },
 *          "footer": {
 *              "body": "string"
 *          }
 *      }
 * )
 */
final class SearchForm extends WidgetTypeBase implements AlterSearchResultsQueryInterface
{

    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var RegionService
     */
    protected $regionService;

    /**
     * @var array
     */
    private $groupFilterTypes = [
        'type',
        'location',
        'time',
        'extra',
        'facility_filters',
    ];

    /**
     * WidgetTypeBase constructor.
     * @param array $pluginDefinition
     * @param array $configuration
     * @param bool $cleanup
     * @param \Twig_Environment $twig
     * @param TwigPreprocessor $twigPreprocessor
     * @param RendererInterface $renderer
     * @param RequestStack $requestStack
     */
    public function __construct(array $pluginDefinition, array $configuration, bool $cleanup, \Twig_Environment $twig, TwigPreprocessor $twigPreprocessor, RendererInterface $renderer, RequestStack $requestStack, RegionService $regionService)
    {
        parent::__construct($pluginDefinition, $configuration, $cleanup, $twig, $twigPreprocessor, $renderer);
        $this->request = $requestStack->getCurrentRequest();
        $this->regionService = $regionService;

        if (!empty($this->settings['header']['body']) && $this->request) {
            $this->settings['header']['body'] = str_replace('{{ base_url }}', $this->request->getScheme() . '://' . $this->request->getHost() . ':' . $this->request->getPort() . $this->request->getBaseUrl(), $this->settings['header']['body']);
        }

        if (!empty($this->settings['footer']['body']) && $this->request) {
            $this->settings['footer']['body'] = str_replace('{{ base_url }}', $this->request->getScheme() . '://' . $this->request->getHost() . ':' . $this->request->getPort() . $this->request->getBaseUrl(), $this->settings['footer']['body']);
        }
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
            $container['request_stack'],
            $container['widget_region_service']
        );
    }
    public function render($cdbid = '', $preferredLanguage = 'nl')
    {
        return $this->twig->render(
            'widgets/search-form-widget/search-form-widget.html.twig',
            [
                'id' => $this->id,
                'settings_general' => $this->settings['general'],
                'settings_header' => $this->settings['header'],
                'settings_footer' => $this->settings['footer'],
                'settings_fields' => $this->settings['fields'],
                'defaults' => $this->getDefaults(),
                'when_autocomplete_path' => $this->request->getScheme() . '://' . $this->request->getHost() . ':' . $this->request->getPort() . $this->request->getBaseUrl() . '/widgets/autocomplete/regions',
                'preferredLanguage' => ($preferredLanguage) ?: 'nl',
            ]
        );
    }

    public function renderPlaceholder($preferredLanguage = 'nl')
    {
        $this->renderer->attachJavascript(WWW_ROOT . '/assets/js/widgets/search-form/search-form.js');
        $this->renderer->attachJavascript(WWW_ROOT . '/assets/js/widgets/search-form/autocomplete.js');

        if ($this->settings['fields']['time']['date_search']['enabled']) {
            $this->renderer->attachJavascript(WWW_ROOT . '/assets/vendor/pickaday/pickaday.js');
            $this->renderer->attachCss(WWW_ROOT . '/assets/vendor/pickaday/pickaday.css');
        }

        $this->renderer->attachJavascript(__DIR__ . '/../../../web/assets/js/widgets/search-form/search-form.js');

        return $this->render('', $preferredLanguage);
    }

    /**
     * Get the default configured values.
     */
    protected function getDefaults()
    {
        $defaults = [];
        if ($this->settings['fields']['time']['date_search']['enabled']) {
            $defaults['when'] = $this->settings['fields']['time']['date_search']['default_option'];
        }

        foreach ($this->groupFilterTypes as $typeKey => $type) {
            if (isset($this->settings['fields'][$type]['group_filters']) &&
                $this->settings['fields'][$type]['group_filters']['enabled']) {
                foreach ($this->settings['fields'][$type]['group_filters']['filters'] as $key => $groupFilter) {
                    $defaults[$typeKey]['group_filters'][$key] = -1;
                    if ($groupFilter['type'] !== 'select_multiple' && isset($groupFilter['default_option'])) {
                        foreach ($groupFilter['options'] as $optionKey => $option) {
                            if (!empty($option['label']) && $option['label'] === $groupFilter['default_option']) {
                                $defaults[$typeKey]['group_filters'][$key] = $optionKey;
                            }
                        }
                    }
                }
            }

            if (isset($this->settings['fields']['facility_filters']) && $this->settings['fields']['facility_filters']['enabled']) {
                foreach ($this->settings['fields']['facility_filters']['filters'] as $groupKey => $facilityGroupFilter) {
                    $defaults['facility_filters'][$groupKey] = -1;
                    foreach ($facilityGroupFilter['options'] as $key => $facilityFilter) {
                        if ($facilityGroupFilter['type'] !== 'select_multiple' && isset($facilityGroupFilter['default_option'])) {
                            if (!empty($option['label']) && $option['label'] === $facilityGroupFilter['default_option']) {
                                $defaults[$typeKey][$key] = $key;
                            }
                        }
                    }
                }
            }
        }

        return $defaults;
    }

    public function alterSearchResultsQuery(SearchResultsQueryAlter $searchResultsQueryAlter, $preferredLanguage = 'nl')
    {

        // Check what filters should be placed active.
        $activeFilters = $this->getDefaults();
        if ($this->request->query->has('search-form')) {
            $searchFormFilters = $this->request->query->get('search-form');
            if (isset($searchFormFilters[$this->id])) {
                foreach ($searchFormFilters[$this->id] as $key => $activeFilter) {
                    // Loop through every custom group filter that is found in query string.
                    if ($key === 'custom' && is_array($activeFilter)) {
                        foreach ($activeFilter as $groupFilterKey => $groupFilterGroups) {
                            if (is_array($groupFilterGroups)) {
                                foreach ($groupFilterGroups as $groupKey => $groupFilterSubmittedValue) {
                                    if (!is_numeric($groupFilterSubmittedValue)) {
                                        $activeFilters[$groupFilterKey]['group_filters'][$groupKey] = explode('|', $groupFilterSubmittedValue);
                                    } else {
                                        $activeFilters[$groupFilterKey]['group_filters'][$groupKey] = [$groupFilterSubmittedValue];
                                    }
                                }
                            }
                        }
                    } elseif ($key === 'facility' && is_array($activeFilter)) {
                        foreach ($activeFilter as $facilityFilterGroupKey => $facilityFilterGroups) {
                            if (!is_numeric($facilityFilterGroups)) {
                                $activeFilters['facility_filters'][$facilityFilterGroupKey] = explode('|', $facilityFilterGroups);
                            } else {
                                $activeFilters['facility_filters'][$facilityFilterGroupKey] = [$facilityFilterGroups];
                            }
                        }
                    } elseif (!empty($activeFilter)) {
                        $activeFilters[$key] = $activeFilter;
                    }
                }
            }
        }
        // Add every active filter to the query.
        $advancedQuery = [];
        $searchResultsActiveFilters = $searchResultsQueryAlter->getActiveFilters();
        foreach ($activeFilters as $key => $activeValue) {
            // Group filters => Search the options related with the default option.
            if (is_numeric($key) && isset($activeFilters[$key]['group_filters'])) {
                if (isset($this->groupFilterTypes[$key])) {
                    $type = $this->groupFilterTypes[$key];
                    foreach ($activeFilters[$key]['group_filters'] as $groupFilterKey => $selectedOptions) {
                        // When no search was done yet, this option is a single value.
                        if (!is_array($selectedOptions)) {
                            $selectedOptions = [$selectedOptions];
                        }

                        if (isset($this->settings['fields'][$type]['group_filters']['filters'][$groupFilterKey])) {
                            $groupFilter = $this->settings['fields'][$type]['group_filters']['filters'][$groupFilterKey];
                            foreach ($selectedOptions as $selectedOption) {
                                if (isset($groupFilter['options'][$selectedOption]) && !empty($groupFilter['options'][$selectedOption]['query'])) {
                                    $advancedQuery[] = $groupFilter['options'][$selectedOption]['query'];
                                    $searchResultsActiveFilters[] = [
                                        'value' => $groupFilter['options'][$selectedOption]['query'],
                                        'label' => $groupFilter['options'][$selectedOption]['label'],
                                        'name' => 'search-form[' . $this->id . '][custom][' . $key . '][' . $groupFilterKey . ']',
                                        'is_default' => $groupFilter['default_option'] === $groupFilter['options'][$selectedOption]['label'],

                                    ];
                                }
                            }
                        }
                    }
                }
            } elseif ($key === 'facility_filters') {
                foreach ($activeFilters[$key] as $facilityGroupKey => $selectedOptions) {
                    // When no search was done yet, this option is a single value.
                    if (!is_array($selectedOptions)) {
                        $selectedOptions = [$selectedOptions];
                    }
                    if (isset($this->settings['fields']['facility_filters']['filters'][$facilityGroupKey])) {
                        $facilityFilter = $this->settings['fields']['facility_filters']['filters'][$facilityGroupKey];
                        foreach ($selectedOptions as $selectedOption) {
                            if (isset($facilityFilter['options'][$selectedOption]) && !empty($facilityFilter['options'][$selectedOption]['query'])) {
                                $advancedQuery[] = $facilityFilter['options'][$selectedOption]['query'];
                                $searchResultsActiveFilters[] = [
                                    'value' => $facilityFilter['options'][$selectedOption]['query'],
                                    'label' => $facilityFilter['options'][$selectedOption]['label'],
                                    'name' => 'search-form[' . $this->id . '][facility][' . $facilityGroupKey . ']',
                                    'is_default' => $facilityFilter['default_option'] === $facilityFilter['options'][$selectedOption]['label'],
                                ];
                            }
                        }
                    }
                }
            } elseif ($key === 'when') {
                // Custom date requested? Construct the date range.
                if ($activeValue === 'custom_date') {
                    $cetTimezone = new \DateTimeZone('CET');
                    $query = '';
                    $labelParts = [$this->twigPreprocessor->translateLabel('activities', 'messages', $preferredLanguage)];
                    if (isset($activeFilters['date-start'])) {
                        $dateTime = \DateTime::createFromFormat('d/m/Y', $activeFilters['date-start'], $cetTimezone);
                        if ($dateTime) {
                            $dateTime->setTime(0, 0, 0);
                            $query .= $dateTime->format('c');
                        }
                        $labelParts[] = $this->twigPreprocessor->translateLabel('from', 'when', $preferredLanguage) . ' ' . $activeFilters['date-start'];
                    } else {
                        $query .= '*';
                    }
                    if (isset($activeFilters['date-end'])) {
                        $dateTime = \DateTime::createFromFormat('d/m/Y', $activeFilters['date-end'], $cetTimezone);
                        if ($dateTime) {
                            $dateTime->setTime(23, 59, 59);
                            $query .= (' TO ' . $dateTime->format('c'));
                            $labelParts[] = $this->twigPreprocessor->translateLabel('to', 'when', $preferredLanguage) . ' ' .  $activeFilters['date-end'];
                        }
                    } else {
                        $query .= ' TO *';
                    }

                    $advancedQuery[] = 'dateRange:[' . $query . ']';

                    $searchResultsActiveFilters[] = [
                        'label' => implode(' ', $labelParts),
                        'name' => 'search-form[' . $this->id . '][when]',
                        'is_default' => false,
                    ];
                } else {
                    // Create ISO-8601 daterange from datetype.
                    $dateRange = $this->convertDateTypeToDateRange($activeValue);
                    if (!empty($dateRange)) {
                        $advancedQuery[] = 'dateRange:' . $dateRange['query'];

                        $searchResultsActiveFilters[] = [
                            'label' => $this->twigPreprocessor->translateLabel($activeValue, 'when', $preferredLanguage),
                            'name' => 'search-form[' . $this->id . '][when]',
                            'is_default' => false,
                        ];
                    }
                }
            } elseif ($key === 'what') {
                $advancedQuery[] = str_replace(' ', ' AND ', $activeValue);
                $searchResultsActiveFilters[] = [
                    'label' => $activeValue,
                    'name' => 'search-form[' . $this->id . '][what]',
                    'is_default' => false,
                ];
            } elseif ($key === 'where') {
                if ($preferredLanguage === 'nl') {
                    $region = $this->regionService->getItemByName($activeValue);
                } else {
                    $region = $this->regionService->getItemByTranslatedName($activeValue, $preferredLanguage);
                }
                if ($region) {
                    $searchResultsActiveFilters[] = [
                        'label' => $region->name,
                        'name' => 'search-form[' . $this->id . '][where]',
                        'is_default' => false,
                    ];
                    $advancedQuery[] = 'regions:' . $region->key;
                }
            }
        }

        if (!empty($advancedQuery)) {
            $searchResultsQueryAlter->setActiveFilters($searchResultsActiveFilters);
            $searchResultsQueryAlter->getSearchQuery()->addParameter(
                new Query(implode($advancedQuery, ' AND '))
            );
        }
    }
}
