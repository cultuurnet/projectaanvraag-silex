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
    public function render($preferredLanguage = 'nl')
    {
        return $this->twig->render(
            'layouts/two-col-sidebar-right/two-col-sidebar-right.html.twig',
            [
                'content' => $this->renderRegion('content', $preferredLanguage),
                'right' => $this->renderRegion('sidebar_right', $preferredLanguage),
            ]
        );
    }
}
