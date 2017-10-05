<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use Pimple\Container;

/**
 * Provides the search form widget type.
 *
 * @WidgetType(
 *      id = "search-form",
 *      defaultSettings = {
 *          "general": {
 *              "new_window": false,
 *              "button_label": "Zoeken"
 *          },
 *          "header": {
 *              "body": "<p>Uit in ...</p>",
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
 *                      "label": "Wat",
 *                      "placeholder": "Bv. concert, Bart Peeters,...",
 *                  },
 *                  "group_filters": {
 *                      "enabled": false
 *                  }
 *              },
 *              "time": {
 *                  "date_search": {
 *                      "enabled" : true,
 *                      "label": "Waar",
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
 *                  }
 *              },
 *          },
 *          "footer": {
 *              "body": "<a href='http://www.uitinvlaanderen.be' target='_blank'><img border='0' class='cultuurnet-logo-uiv' src='http://tools.uitdatabank.be/sites/all/modules/cul_widgets_server/images/uiv-btn.jpg' alt='Meer tips op UiTinVlaanderen.be' /></a>"
 *          }
 *      },
 *      allowedSettings = {
 *          "general": {
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
 *          },
 *          "footer": {
 *              "body": "string"
 *          }
 *      }
 * )
 */
class SearchForm extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {

        return $this->twig->render(
            'widgets/search-form-widget/search-form-widget.html.twig',
            [
                'settings_general' => $this->settings['general'],
                'settings_header' => $this->settings['header'],
                'settings_footer' => $this->settings['footer'],
                'settings_fields' => $this->settings['fields'],
                'defaults' => $this->getDefaults(),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        /*$this->renderer->attachJavascript(__DIR__ . '/../../../web/assets/js/widgets/search-form/search-form.js');
        $this->renderer->attachCss(__DIR__ . '/../../../web/assets/css/widgets/search-form/search-form.css');*/

        return $this->render();
    }

    /**
     * Get the default values based on current request.
     */
    protected function getDefaults() {
        $defaults = [];
        if ($this->settings['fields']['time']['date_search']['enabled']) {
            $defaults['current_date'] = $this->settings['fields']['time']['date_search']['default_option'];
        }

        $group_filter_types = [
            'type',
            'location',
            'time',
            'extra',
        ];

        foreach ($group_filter_types as $type) {
            if ($this->settings['fields'][$type]['group_filters']['enabled']) {
                foreach ($this->settings['fields'][$type]['group_filters']['filters'] as $key => $group_filter) {
                    $defaults[$type]['group_filters'][$key] = $group_filter['default_option'] ?? '';
                }
            }
        }

        return $defaults;
    }
}
