<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

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
 *              }
 *          },
 *          "search_params" : {
 *              "query": "string"
 *          }
 *     }
 * )
 */
class SearchResults extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->twig->render('widgets/search-results-widget/search-results-widget.html.twig', []);
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
