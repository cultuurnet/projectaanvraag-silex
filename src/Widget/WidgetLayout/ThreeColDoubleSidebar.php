<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\Widget\Annotation\Layout;

/**
 * Provides a two col layout with a sidebar on the right.
 *
 * @Layout(
 *     id = "3col-double-sidebar"
 * )
 */
class ThreeColDoubleSidebar extends LayoutBase
{
    /**
     * {@inheritdoc}
     */
    public function render($preferredLanguage = 'nl')
    {
        return $this->twig->render(
            'layouts/three-col-double-sidebar/three-col-double-sidebar.html.twig',
            [
                'content' => $this->renderRegion('content', $preferredLanguage),
                'right' => $this->renderRegion('sidebar_right', $preferredLanguage),
                'left' => $this->renderRegion('sidebar_left', $preferredLanguage),
            ]
        );
    }
}
