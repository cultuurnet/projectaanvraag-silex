<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\Widget\Annotation\Layout;

/**
 * Provides a two col layout with a sidebar on the left.
 *
 * @Layout(
 *     id = "2col-sidebar-left"
 * )
 */
class TwoColSidebarLeft extends LayoutBase
{
    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->twig->render(
            'layouts/two-col-sidebar-left/two-col-sidebar-left.html.twig',
            [
            'content' => $this->renderRegion('content'),
            'left' => $this->renderRegion('sidebar_left'),
            ]
        );
    }
}
