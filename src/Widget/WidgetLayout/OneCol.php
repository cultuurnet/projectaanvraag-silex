<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetLayout;

use CultuurNet\ProjectAanvraag\Widget\Annotation\Layout;

/**
 * @Layout(
 *     id = "one-col"
 * )
 */
class OneCol extends LayoutBase
{

    public function render($preferredLanguage = 'nl')
    {
        return $this->twig->render('layouts/one-col/one-col.html.twig', ['content' => $this->renderRegion('content', $preferredLanguage)]);
    }
}
