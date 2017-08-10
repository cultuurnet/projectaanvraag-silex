<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use CultuurNet\SearchV3\PagedCollection;
use CultuurNet\SearchV3\Parameter\Facet;
use CultuurNet\SearchV3\Parameter\Labels;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
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
 *                  "cbdid":"query_string"
 *              }
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
 *          }
 *      },
 *      allowedSettings = {
 *          "general":{
 *              "items":"integer",
 *              "detail_link":{
 *                  "enabled":"boolean",
 *                  "url":"string",
 *                  "cbdid":"string"
 *              }
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
 *          }
 *      }
 * )
 */
class Tips extends WidgetTypeBase
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
        $query = new SearchQuery(true);
        $query->addParameter(new Facet('regions'));
        $query->addParameter(new Facet('types'));
        $query->addParameter(new Labels('bouwen'));
        $query->addParameter(new Labels('Kiditech'));
        $query->addParameter(new Query('regions:gem-leuven OR regions:gem-gent'));

        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        $result = $this->searchClient->searchEvents($query);
        return $this->twig->render('widgets/tips-widget/tips-widget.html.twig');
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
