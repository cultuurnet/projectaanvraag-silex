<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Guzzle\Cache\FixedTtlCacheStorage;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetRowEntity;
use CultuurNet\ProjectAanvraag\Widget\JavascriptResponse;
use CultuurNet\ProjectAanvraag\Widget\LayoutDiscovery;
use CultuurNet\ProjectAanvraag\Widget\LayoutManager;
use CultuurNet\ProjectAanvraag\Widget\RegionService;
use CultuurNet\ProjectAanvraag\Widget\Renderer;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\Facets;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeDiscovery;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
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
     * @var string
     */
    protected $legacyHost;

    /**
     * @var RegionService
     */
    protected $regionService;

    /**
     * WidgetController constructor.
     *
     * @param RendererInterface $renderer
     * @param DocumentRepository $widgetRepository
     * @param Connection $db
     */
    public function __construct(RendererInterface $renderer, DocumentRepository $widgetRepository, Connection $db, SearchClient $searchClient, WidgetPageEntityDeserializer $widgetPageEntityDeserializer, bool $debugMode, string $legacyHost, RegionService $regionService)
    {
        $this->renderer = $renderer;
        $this->widgetRepository = $widgetRepository;
        $this->searchClient = $searchClient;
        $this->widgetPageEntityDeserializer = $widgetPageEntityDeserializer;
        $this->debugMode = $debugMode;
        $this->legacyHost = $legacyHost;
        $this->regionService = $regionService;
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
        // Determine directory path to store js files.
        $directory = dirname(WWW_ROOT . $request->getPathInfo());

        // Check if page is from old version.
        if ($widgetPage->getVersion() != 3) {
            $pageId = $widgetPage->getId();

            // Check if js file exists.
            if (file_exists("$directory/$pageId.js")) {
                $jsContent = file_get_contents("$directory/$pageId.js");
            }
            else {
                // Retrieve file from old host URL.
                $jsContent = file_get_contents("$this->legacyHost/widgets/layout/$pageId.js");
            }
        }
        else {
            $javascriptResponse = new JavascriptResponse($this->renderer, $this->renderer->renderPage($widgetPage));
            $jsContent = $javascriptResponse->getContent();
        }

        // Only write the javascript files, when we are not in debug mode.
        if (!$this->debugMode) {
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

        $data = [
            'data' => $this->renderer->renderWidget($this->getWidget($widgetPage, $widgetId)),
        ];
        $response = new JsonResponse($data);

        // If this is a jsonp request, set the requested callback.
        if ($request->query->has('callback')) {
            $response->setCallback($request->query->get('callback'));
        }

        return $response;
    }

    /**
     * Render the given search results widget + all related facets and return it as a json response.
     *
     * @param Request $request
     * @param WidgetPageInterface $widgetPage
     * @param $widgetId
     * @return JsonResponse
     */
    public function renderSearchResultsWidgetWithFacets(Request $request, WidgetPageInterface $widgetPage, $widgetId)
    {

        $searchResultsWidget = null;
        $facetWidgets = [];
        $rows = $widgetPage->getRows();

        // Search for the requested widget and facets that apply to it.
        foreach ($rows as $row) {
            $widgets = $row->getWidgets();
            foreach ($widgets as $id => $widget) {
                if ($id === $widgetId) {
                    $searchResultsWidget = $widget;
                }

                // Apply the facet
                if ($widget instanceof Facets && $widget->getTargetedSearchResultsWidgetId() === $widgetId) {
                    $facetWidgets[$id] = $widget;
                }
            }
        }

        // If the widget is not a search result. This method should return 404.
        if (empty($searchResultsWidget) || !$searchResultsWidget instanceof SearchResults) {
            throw new NotFoundHttpException();
        }

        $renderedWidgets = [
            'search_results' => $this->renderer->renderWidget($searchResultsWidget),
            'facets' => [],
        ];

        $searchResult = $searchResultsWidget->getSearchResult();
        foreach ($facetWidgets as $facetWidgetId => $facetWidget) {
            $facetWidget->setSearchResult($searchResult);
            $renderedWidgets['facets'][$facetWidgetId] = $this->renderer->renderWidget($facetWidget);
        }

        $data = [
            'data' => $renderedWidgets,
        ];
        $response = new JsonResponse($data);

        // If this is a jsonp request, set the requested callback.
        if ($request->query->has('callback')) {
            $response->setCallback($request->query->get('callback'));
        }

        return $response;
    }

    /**
     * Render the detailpage of an event, based on the settings for a given widget.
     *
     * @param Request $request
     * @param WidgetPageInterface $widgetPage
     * @param $widgetId
     */
    public function renderDetailPage(Request $request, WidgetPageInterface $widgetPage, $widgetId)
    {

        $widget = $this->getWidget($widgetPage, $widgetId);
        if (!$widget instanceof SearchResults) {
            throw new NotFoundHttpException();
        }

        $data = [
            'data' => $this->renderer->renderDetailPage($widget),
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
     * Get the given widget from the widget page.
     * @return WidgetTypeInterface
     * @throws NotFoundHttpException
     */
    private function getWidget(WidgetPageInterface $widgetPage, $widgetId)
    {
        $widget = null;
        $rows = $widgetPage->getRows();

        // Search for the requested widget.
        foreach ($rows as $row) {
            if ($row->hasWidget($widgetId)) {
                return $row->getWidget($widgetId);
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Provide autocompletion results for regions.
     * @param $searchString
     */
    public function getRegionAutocompleteResult($searchString)
    {
        $matches = $this->regionService->getAutocompletResults($searchString);

        // Only return 5 matches.
        return new JsonResponse(array_slice($matches, 0, 5));
    }
}
