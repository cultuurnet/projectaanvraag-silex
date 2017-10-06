<?php

namespace CultuurNet\ProjectAanvraag\ShareProxy\Controller;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetRowEntity;
use CultuurNet\ProjectAanvraag\Widget\JavascriptResponse;
use CultuurNet\ProjectAanvraag\Widget\LayoutDiscovery;
use CultuurNet\ProjectAanvraag\Widget\LayoutManager;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use CultuurNet\SearchV3\PagedCollection;
use CultuurNet\SearchV3\Parameter\Facet;
use CultuurNet\SearchV3\Parameter\Labels;
use CultuurNet\SearchV3\Parameter\Query;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchQuery;
use CultuurNet\SearchV3\SearchQueryInterface;
use CultuurNet\SearchV3\Serializer\Serializer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializerBuilder;
use ML\JsonLD\JsonLD;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;
use SimpleBus\JMSSerializerBridge\JMSSerializerObjectSerializer;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /**
     * WidgetController constructor.
     *
     * @param RendererInterface $renderer
     * @param DocumentRepository $widgetRepository
     * @param Connection $db
     */
    public function __construct(RendererInterface $renderer, DocumentRepository $widgetRepository, Connection $db, SearchClient $searchClient, WidgetPageEntityDeserializer $widgetPageEntityDeserializer, \Twig_Environment $twig, RequestStack $requestStack, bool $debugMode)
    {
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
     */
    public function socialShareProxy($cdbid) {
        // Get origin url.
        $originUrl = ($this->request->query->get('origin') ? $this->request->query->get('origin') : '');

        // Retrieve event corresponding to ID.
        $query = new SearchQuery(true);
        $query->addParameter(
            new Query($cdbid)
        );
        // Retrieve results from Search API.
        $result = $this->searchClient->searchEvents($query);
        $items = $result->getMember()->getItems();

        if (!empty($items)) {
            $event = $items[0];
            return $this->twig->render(
                'share-proxy/share-proxy.html.twig',
                [
                    'name' => $event->getName()['nl'],
                    'description' => $event->getDescription()['nl'],
                    'image' => $event->getImage(),
                    'url' => $originUrl
                ]
            );
        }
    }
}
