<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy\Controller;

use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\ValueObjects\Offer;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a controller to render a share proxy page.
 */
class ShareProxyController
{

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var DocumentRepository
     */
    protected $widgetRepository;

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * @var WidgetPageEntityDeserializer
     */
    protected $widgetPageEntityDeserializer;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var RequestStack
     */
    protected $request;

    /**
     * @var bool
     */
    protected $debugMode;

    public function __construct(
        RendererInterface $renderer,
        DocumentRepository $widgetRepository,
        SearchClient $searchClient,
        WidgetPageEntityDeserializer $widgetPageEntityDeserializer,
        \Twig_Environment $twig,
        RequestStack $requestStack,
        bool $debugMode
    ) {
        $this->renderer = $renderer;
        $this->widgetRepository = $widgetRepository;
        $this->searchClient = $searchClient;
        $this->widgetPageEntityDeserializer = $widgetPageEntityDeserializer;
        $this->twig = $twig;
        $this->request = $requestStack->getCurrentRequest();
        $this->debugMode = $debugMode;
    }

    /**
     * Social share proxy page.
     *
     * @param Offer $offer
     * @return string
     */
    public function socialShareProxy($offer)
    {
        $langcode = 'nl';
        // Get origin url.
        $originUrl = ($this->request->query->get('origin') ? $this->request->query->get('origin') : '');
        return $this->twig->render(
            'share-proxy/share-proxy.html.twig',
            [
                'name' => $offer->getName()->getValueForLanguage($langcode),
                'description' => $offer->getDescription() !== null ? $offer->getDescription()->getValueForLanguage($langcode) : '',
                'image' => $offer->getImage(),
                'url' => $originUrl,
                'request_url' => $this->request->getUri(),
            ]
        );
    }
}
