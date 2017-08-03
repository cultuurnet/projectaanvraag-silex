<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;
use Pimple\Container;

/**
 * Provides the html widget type.
 *
 * @WidgetType(
 *      id = "html",
 *      defaultSettings = {
 *      },
 *      allowedSettings = {
 *          "content": {
 *              "body": "string",
 *          },
 *      }
 * )
 */
class Html extends WidgetTypeBase
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
        return 'html widget';
    }
}
