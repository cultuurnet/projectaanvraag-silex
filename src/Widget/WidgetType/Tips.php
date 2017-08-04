<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use Pimple\Container;

/**
 * Provides the tips widget type.
 *
 * @WidgetType(
 *      id = "tips",
 *      defaultSettings = {
 *      },
 *      allowedSettings = {
 *      }
 * )
 */
class Tips extends WidgetTypeBase
{

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $this->renderPlaceholder();
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return 'tips widget';
    }
}
