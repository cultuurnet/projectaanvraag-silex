<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\Widget\Annotation\Layout;

/**
 * Provides a two col layout with a sidebar on the right.
 *
 * @Layout(
 *     id = "2col-sidebar-right"
 * )
 */
class TwoColSidebarRight extends LayoutBase
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->twig->render(
            'layouts/two-col-sidebar-right/two-col-sidebar-right.html.twig',
            [
                'content' => $this->renderRegion('content'),
                'right' => $this->renderRegion('sidebar_right'),
            ]
        );
    }
}
