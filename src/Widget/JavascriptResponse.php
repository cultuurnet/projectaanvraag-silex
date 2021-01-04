<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use MatthiasMullie\Minify\Minify;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a response class for javascript responses.
 */
class JavascriptResponse extends Response
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var WidgetPageInterface
     */
    protected $widgetPage;

    /**
     * JavascriptResponse constructor.
     *
     * @param Request $request
     * @param RendererInterface $renderer
     * @param int $content
     * @param WidgetPageInterface $widgetPage
     */
    public function __construct(Request $request, RendererInterface $renderer, $content, WidgetPageInterface $widgetPage)
    {

        $this->request = $request;
        $this->widgetPage = $widgetPage;

        if ($content) {
            $content = $this->renderContent($content);
        } else {
            $content = '';
        }

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
    {   $widgetWrapperId = "uit-widget";
        return 'if(document.getElementById("'. $widgetWrapperId .'")){document.getElementById("'. $widgetWrapperId .'").innerHTML = "' . addslashes($content) . '";}else{document.write("' . trim(preg_replace('~[\r\n]+~', ' ', addslashes($content))) . '");}';
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

        /* Include custom user-css */
        $cssMinify->add($this->widgetPage->getCss());

        $cssPath = 'widgets/layout/' . $this->widgetPage->getId() . '.css';
        $cssMinify->minify(WWW_ROOT . '/' . $cssPath);

        $cssUrl = $this->request->getScheme() . '://' . $this->request->getHost() . $this->request->getBaseUrl() . '/' . $cssPath;

        $cssFont = "https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i,800";

        return 'CultuurnetWidgets.addExternalStyle("' . $cssUrl .'");CultuurnetWidgets.addExternalStyle("'.$cssFont.'")';
    }
}
