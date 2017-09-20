<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

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

/**
 * Provides a controller to render widget pages and widgets.
 */
class WidgetController
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
     * @var bool
     */
    protected $debugMode;

    /**
     * @var array
     */
    protected $config;

    /**
     * WidgetController constructor.
     *
     * @param RendererInterface $renderer
     * @param DocumentRepository $widgetRepository
     * @param Connection $db
     */
    public function __construct(RendererInterface $renderer, DocumentRepository $widgetRepository, Connection $db, SearchClient $searchClient, WidgetPageEntityDeserializer $widgetPageEntityDeserializer, bool $debugMode, array $config)
    {
        $this->renderer = $renderer;
        $this->widgetRepository = $widgetRepository;
        $this->searchClient = $searchClient;
        $this->widgetPageEntityDeserializer = $widgetPageEntityDeserializer;
        $this->debugMode = $debugMode;
        $this->config = $config;


/*        $json = file_get_contents(__DIR__ . '/../../../test/Widget/data/page.json');
        $doc = json_decode($json, true);
        $collection->insert($doc);
        die();*/

/*        $layoutDiscovery = new LayoutDiscovery();
        $layoutDiscovery->register(__DIR__ . '/../WidgetLayout', 'CultuurNet\ProjectAanvraag\Widget\WidgetLayout');

        $typeDiscovery = new WidgetTypeDiscovery();
        $typeDiscovery->register(__DIR__ . '/../WidgetType', 'CultuurNet\ProjectAanvraag\Widget\WidgetType');

        $layoutManager = new WidgetPluginManager($layoutDiscovery);
        $test = $layoutManager->createInstance('one-col');

        $widgetTypeManager = new WidgetPluginManager($typeDiscovery);
        $test2 = $widgetTypeManager->createInstance('search-form');
print_r($test);
print_r($test2);
        die();*/

        /*$results = $collection->find();
        while ($results->hasNext()) {
            $document = $results->getNext();
            //print '<pre>' . print_r($document, true) . '</pre>';
        }*/
    }

    /**
     * Render the widget page.
     *
     * @param Request $request
     * @param WidgetPageInterface $widgetPage
     * @return string
     */
    public function renderPage(Request $request, WidgetPageInterface $widgetPage)
    {

        $jsContent = '';
        // Check if page is from old version.
        if ($widgetPage->getVersion() != 3) {
            $legacyBaseUrl = $this->config['legacy_host'];
            $jsContent = 'load old js file tools.uitdatabank';
            // TODO: check if js file exists (root)

            // TODO: if not, download js file
            // TODO: \-> save js file
        }
        else {
            $javascriptResponse = new JavascriptResponse($this->renderer, $this->renderer->renderPage($widgetPage));
            $jsContent = $javascriptResponse->getContent();
        }

        // Only write the javascript files, when we are not in debug mode.
        if (!$this->debugMode) {
            $directory = dirname(WWW_ROOT . $request->getPathInfo());
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            file_put_contents(WWW_ROOT . $request->getPathInfo(), $jsContent);
        }

        return $jsContent;
    }

    /**
     * Render the given widget and return it as a json response.
     *
     * @param Request $request
     * @param WidgetPageInterface $widgetPage
     * @param $widgetId
     * @return JsonResponse
     */
    public function renderWidget(Request $request, WidgetPageInterface $widgetPage, $widgetId)
    {

        $widget = null;
        $rows = $widgetPage->getRows();

        // Search for the requested widget.
        foreach ($rows as $row) {
            if ($row->hasWidget($widgetId)) {
                $widget = $row->getWidget($widgetId);
            }
        }

        if (empty($widget)) {
            throw new NotFoundHttpException();
        }

        $data = [
            'data' => $this->renderer->renderWidget($widget),
        ];
        $response = new JsonResponse($data);
        //$response = new Response($this->renderer->renderWidget($widget));

        // If this is a jsonp request, set the requested callback.
        if ($request->query->has('callback')) {
            $response->setCallback($request->query->get('callback'));
        }

        return $response;
    }

    /**
     * Example of a search request.
     */
    public function searchExample()
    {
        $query = new SearchQuery(true);
        $query->addParameter(new Facet('regions'));
        $query->addParameter(new Facet('types'));
        //$query->addParameter(new Labels('bouwen'));
        //$query->addParameter(new Labels('Kiditech'));
        $query->addParameter(new Query('regions:gem-leuven OR regions:gem-gent'));

        $query->addSort('availableTo', SearchQueryInterface::SORT_DIRECTION_ASC);

        $result = $this->searchClient->searchEvents($query);
        print_r($result);
        die();
    }
}
