<?php

namespace CultuurNet\ProjectAanvraag\Widget\WidgetType;

use CultuurNet\ProjectAanvraag\Utility\TextProcessingTrait;

use CultuurNet\ProjectAanvraag\Widget\Annotation\WidgetType;

/**
 * Provides the html widget type.
 *
 * @WidgetType(
 *      id = "html",
 *      defaultSettings = {
 *         "content":{
 *              "body":""
 *          }
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

    public function render($cdbid = '', $preferredLanguage = 'nl')
    {
        return $this->renderPlaceholder();
    }

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
