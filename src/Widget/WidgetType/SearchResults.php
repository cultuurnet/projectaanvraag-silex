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
 *          "test": {
 *              "enabled" : true,
 *              "label": "Wat",
 *          }
 *      },
 *      allowedSettings = {
 *      }
 * )
 */
class SearchResults extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return 'search results';
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => $this->id]);
    }
}
