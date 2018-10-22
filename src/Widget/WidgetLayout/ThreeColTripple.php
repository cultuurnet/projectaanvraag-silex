<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\Widget\Annotation\Layout;

/**
 * Provides a two col layout with a sidebar on the right.
 *
 * @Layout(
 *     id = "3col-tripple"
 * )
 */
class ThreeColTripple extends LayoutBase
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->twig->render(
            'layouts/three-col-tripple/three-col-tripple.html.twig',
            [
                'content' => $this->renderRegion('content'),
                'right' => $this->renderRegion('sidebar_right'),
                'left' => $this->renderRegion('sidebar_left'),
            ]
        );
    }
}
