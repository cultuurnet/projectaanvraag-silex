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
 *          'general': {
 *              'new_window': false,
 *              'button_label': 'Zoeken'
 *          },
 *          'header': {
 *              'body': '<p>Uit in ...</p>',
 *          },
 *          'fields': {
 *              'type': {
 *                  'keyword_search': {
 *                  'enabled' : true,
 *                  'label': 'Wat',
 *                  'placeholder': 'Bv. concert, Bart Peeters,...',
 *              },
 *              'group_filters': {
 *                  'enabled': false,
 *              }
 *          },
 *          'location': {
 *              'keyword_search': {
 *                  'enabled' : true,
 *                  'label': 'Wat',
 *                  'placeholder': 'Bv. concert, Bart Peeters,...',
 *              },
 *              'group_filters': {
 *                  'enabled': false
 *                }
 *          },
 *          'time': {
 *              'date_search': {
 *                  'enabled' : true,
 *                  'options': {
 *                      'today': true,
 *                      'tomorrow': true,
 *                      'weekend': true,
 *                      'days_7': true,
 *                      'days_14': true,
 *                      'days_30': true,
 *                      'custom_date': true
 *                   }
 *               },
 *              'group_filters': {
 *                  'enabled': false
 *              }
 *          },
 *          'extra': {
 *              'group_filters': {
 *                  'enabled': false
 *              }
 *          }
 *      },
 *      'footer': {
 *          'body': '<a href="http://www.uitinvlaanderen.be" target="_blank"><img border="0" class="cultuurnet-logo-uiv" src="http://tools.uitdatabank.be/sites/all/modules/cul_widgets_server/images/uiv-btn.jpg" alt="Meer tips op UiTinVlaanderen.be" /></a>'
 *      }
 *
 *      },
 *      allowedSettings = {
 *          "destination": "string",
 *          "new_window": "boolean",
 *          "button_label": "string",
 *          "search_query": "string",
 *          "header": {
 *              "body": "string",
 *          },
 *          "fields": {
 *              "what": {
 *                  "keyword_search": {
 *                      "enabled": "boolean",
 *                      "label": "string",
 *                      "placeholder": "string"
 *                  },
 *                  "group_filters": "CultuurNet\ProjectAanvraag\Widget\Settings\GroupFilter"
 *              }
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
        return 'search form';
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        // @todo Move to twig extension.
        $this->renderer->attachJavascript(__DIR__ . '/../../../web/assets/js/widgets/search-form/search-form.js');
        $this->renderer->attachCss(__DIR__ . '/../../../web/assets/css/widgets/search-form/search-form.css');

        return $this->twig->render('widgets/search-form-widget/search-form-widget.html.twig');
    }
}
