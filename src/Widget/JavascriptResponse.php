<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\Minify;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a response class for javascript responses.
 */
class JavascriptResponse extends Response
{

    /**
     * JavascriptResponse constructor.
     * @param RendererInterface $renderer
     * @param int $content
     */
    public function __construct(RendererInterface $renderer, $content)
    {
        $content = $this->renderContent($content);
        $content .= $this->renderJs($renderer);

        $content .= "CultuurnetWidgets.prepareBootstrap();";

        parent::__construct($content);
    }

    /**
     * Render given content
     * @param string $content
     * @return string
     */
    private function renderContent($content)
    {
        return 'document.write("' . trim(preg_replace('~[\r\n]+~', ' ', addslashes($content))) . '");';
    }

    /**
     * Render the javascript.
     * @param RendererInterface $renderer
     * @return string
     */
    private function renderJs(RendererInterface $renderer)
    {

        $attachments = $renderer->getAttachedJs();
        if (empty($attachments)) {
            return;
        }

        $jsMinify = new JS();
        foreach ($attachments as $js) {
            $jsMinify->add($js['value']);
        }

        // Css is printed via js method in the js response.
        $jsMinify->add($this->renderCss($renderer));

        return $jsMinify->minify() . ";";
    }

    /**
     * Render the css.
     * @param RendererInterface $renderer
     * @return string
     */
    private function renderCss(RendererInterface $renderer)
    {

        $attachments = $renderer->getAttachedCss();
        if (empty($attachments)) {
            return;
        }

        $cssMinify = new CSS();
        foreach ($attachments as $css) {
            $cssMinify->add($css['path']);
        }

        return 'CultuurnetWidgets.addStyle("' . addslashes($cssMinify->minify()) .'");';
    }
}
