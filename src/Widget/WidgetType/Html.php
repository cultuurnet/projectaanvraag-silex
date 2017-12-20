<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Utility\TextProcessingTrait;
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
 *         "content":{
 *              "body":"string"
 *          }
 *      }
 * )
 */
class Html extends WidgetTypeBase
{

    use TextProcessingTrait;

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return $this->renderPlaceholder();
    }

    /**
     * {@inheritdoc}
     */
    public function renderPlaceholder()
    {
        return $this->twig->render(
            'widgets/html-widget/html-widget.html.twig',
            [
                'id' => $this->id,
                'html' => isset($this->settings['content']['body']) ? $this->filterXss($this->settings['content']['body']) : '',
            ]
        );
    }
}
