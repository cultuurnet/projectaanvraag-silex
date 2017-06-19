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
 *     id = "search-form"
 * )
 */
class SearchForm extends WidgetTypeBase
{

    /**
     * @inheritDoc
     */
    public function render()
    {
        return 'search form';
    }

    /**
     * @inheritDoc
     */
    public function renderPlaceholder()
    {
        // @todo Move to twig extension.
        $this->renderer->attachJavascript(__DIR__ . '/../../../web/assets/js/widgets/search-form/search-form.js');
        $this->renderer->attachCss(__DIR__ . '/../../../web/assets/css/widgets/search-form/search-form.css');

        return $this->twig->render('widgets/search-form-widget/search-form-widget.html.twig');
    }
}
