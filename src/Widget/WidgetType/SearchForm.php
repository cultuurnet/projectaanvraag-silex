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
 *          "fields": {
 *              "what": {
 *                  "keyword_search": {
 *                      "enabled" : true,
 *                      "label": "Wat",
 *                      "placeholder": "Bv. concert, Bart Peeters,..."
 *                  },
 *                  "group_filters": {
 *                      "enabled": false,
 *                  }
 *              },
 *          }
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
