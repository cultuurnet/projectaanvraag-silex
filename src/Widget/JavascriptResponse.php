<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\Minify;
use Symfony\Component\HttpFoundation\Response;

class JavascriptResponse extends Response
{

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
     */
    private function renderJs(RendererInterface $renderer)
    {

        $attachments = $renderer->getAttachedJs();
        if (empty($attachments)) {
            return;
        }

        $jsMinify = new JS();
        foreach ($attachments as $js) {
            $jsMinify->add($js);
        }

        // Css is printed via js method in the js response.
        $jsMinify->add($this->renderCss($renderer));

        return $jsMinify->minify() . ";";
    }

    /**
     * Render the css.
     */
    private function renderCss(RendererInterface $renderer)
    {

        $attachments = $renderer->getAttachedCss();
        if (empty($attachments)) {
            return;
        }

        $cssMinify = new CSS();
        foreach ($attachments as $css) {
            $cssMinify->add($css);
        }

        // @todo Take decision: Save minified css locally and load it via stylesheet tag, or embed it as inline css. Current version => inline
        return 'CultuurnetWidgets.addStyle("' . $cssMinify->minify() .'");';
    }
}
