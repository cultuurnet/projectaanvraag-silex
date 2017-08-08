<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use Pimple\Container;

/**
 * Provides the facets widget type.
 *
 * @WidgetType(
 *      id = "facets",
 *      defaultSettings = {
 *            "filters":{
 *                  "what":true,
 *                  "where":true,
 *                  "when":false,
 *              },
 *              "group_filters":{
 *              "enabled":false,
 *              "filters":[
 *              ]
 *          }
 *      },
 *      allowedSettings = {
 *           "id":"string",
 *           "name":"string",
 *           "type":"string",
 *           "settings":{
 *                 "filters":{
 *                       "what":"boolean",
 *                      "where":"boolean",
 *                      "when":"boolean"
 *                   },
 *                  "group_filters":{
 *                  "enabled":"boolean",
 *                  "filters":[
 *                    {
 *                        "label":"string",
 *                        "type":"link",
 *                        "placeholder":"string",
 *                        "options":[
 *                            {
 *                                "label":"string",
 *                                "query":"string"
 *                            }
 *                        ]
 *                    }
 *                 ]
 *            },
 *           "search_results":"string"
 *           }
 *      }
 * )
 */
class Facets extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->renderPlaceholder();
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return 'facets widget';
    }
}
