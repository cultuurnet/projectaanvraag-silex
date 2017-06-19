<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

/**
 * Provides the search form widget type.
 *
 * @WidgetType(
 *     id = "search-results"
 * )
 */
class SearchResults extends WidgetTypeBase
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        return 'search results';
    }

    /**
     * @inheritDoc
     */
    public function renderPlaceholder()
    {
        return $this->twig->render('widgets/widget-placeholder.html.twig', ['id' => 'search-form-id']);
    }
}
