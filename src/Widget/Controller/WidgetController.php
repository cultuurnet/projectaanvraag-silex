<?php

namespace CultuurNet\ProjectAanvraag\Widget\Controller;

use CultuurNet\ProjectAanvraag\Project\Converter\ProjectConverter;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetRowEntity;
use CultuurNet\ProjectAanvraag\Widget\JavascriptResponse;
use CultuurNet\ProjectAanvraag\Widget\LayoutManager;
use CultuurNet\ProjectAanvraag\Widget\RegionService;
use CultuurNet\ProjectAanvraag\Widget\RendererInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageEntityDeserializer;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\Facets;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;
use CultuurNet\ProjectAanvraag\Widget\WidgetTypeInterface;
use CultuurNet\SearchV3\PagedCollection;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CultuurNet\ProjectAanvraag\ArticleLinker;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use CultuurNet\ProjectAanvraag\ArticleLinker\Command\CreateArticleLink;

/**
 * Provides a controller to render widget pages and widgets.
 */
class WidgetController
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

    /**
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * @var DocumentRepository
     */
    protected $widgetRepository;

    /**
     * @var ProjectConverter
     */
    protected $projectConverter;

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
     * @var Article
     */
    protected $articleLinkerClient;

    /**
     * WidgetController constructor.
     *
     * @param RendererInterface $renderer
     * @param DocumentRepository $widgetRepository
     * @param Connection $db
     * @param MessageBusSupportingMiddleware $commandBus
     */
    public function __construct(
        RendererInterface $renderer,
        DocumentRepository $widgetRepository,
        ProjectConverter $projectConverter,
        Connection $db,
        WidgetPageEntityDeserializer $widgetPageEntityDeserializer,
        bool $debugMode,
        string $legacyHost,
        RegionService $regionService,
        MessageBusSupportingMiddleware $commandBus
    ) {
        $this->renderer = $renderer;
        $this->widgetRepository = $widgetRepository;
        $this->projectConverter = $projectConverter;
        $this->widgetPageEntityDeserializer = $widgetPageEntityDeserializer;
        $this->debugMode = $debugMode;
        $this->legacyHost = $legacyHost;
        $this->regionService = $regionService;
        $this->commandBus = $commandBus;
    }

    public function renderPageForceCurrent(Request $request, WidgetPageInterface $widgetPage)
    {
        return $this->renderPage($request, $widgetPage, true);
    }

    /**
     * Render the widget page.
     *
     * @param Request $request
     * @param WidgetPageInterface $widgetPage
     * @param boolean forceCurrent
     * @return string
     */
    public function renderPage(Request $request, WidgetPageInterface $widgetPage, $forceCurrent = false)
    {
        if (strpos($request->headers->get('referer'), 'forceCurrent=true') !== false) {
            $forceCurrent = true;
        }

        // Determine directory path to store js files.
        $directory = dirname(WWW_ROOT . $request->getPathInfo());
        $pageId = $widgetPage->getId();

        // Check if js file exists.
        if (file_exists($directory . '/' . $pageId . '.js')) {
            return file_get_contents($directory . '/' . $pageId . '.js');
        }

        // Check if page is from old version.
        if ($widgetPage->getVersion() != 3 && !$forceCurrent) {
            // Retrieve file from old host URL.
            $jsContent = file_get_contents($this->legacyHost . '/' . 'widgets/layout/' . $pageId . '.js');
        } else {
            $javascriptResponse = new JavascriptResponse($request, $this->renderer, $this->renderer->renderPage($widgetPage), $widgetPage);
            $jsContent = $javascriptResponse->getContent();
        }

        // Only write the javascript files, when we are not in debug mode.
        if (!$this->debugMode && $jsContent) {
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
     * @param $cdbid
     * @return JsonResponse
     */
    public function renderWidget(Request $request, WidgetPageInterface $widgetPage, $widgetId, $cdbid = '')
    {
        $project = $this->projectConverter->convert($widgetPage->getProjectId());
        $projectActive = $project->getStatus() === ProjectInterface::PROJECT_STATUS_ACTIVE;

        if ($cdbid && $request->headers->get('referer')) {
            $url = $request->headers->get('referer');

            $this->commandBus->handle(new CreateArticleLink($url, $cdbid, $projectActive));
        }

        if (!$project) {
            throw new NotFoundHttpException();
        }
        $this->renderer->setProject($project);

        $preferredLanguage = (!empty($widgetPage->getLanguage())) ? $widgetPage->getLanguage() : 'nl';

        $data = [
            'data' => $this->renderer->renderWidget($this->getWidget($widgetPage, $widgetId), $cdbid, $preferredLanguage),
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

        if ($request->query->has('submitted_page') && $request->query->get('submitted_page') !== $widgetPage->getId()) {
            $submittedPage = $this->widgetRepository->findOneBy(
                [
                    'id' => $request->get('submitted_page'),
                ]
            );

            if ($submittedPage) {
                $request->attributes->set('submittedPage', $submittedPage);
            }
        }

        // Search for the requested widget and facets that apply to it.
        foreach ($rows as $row) {
            $widgets = $row->getWidgets();
            foreach ($widgets as $id => $widget) {
                $id = (string) $id;
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

        $project = $this->projectConverter->convert($widgetPage->getProjectId());
        if (!$project) {
            throw new NotFoundHttpException();
        }
        $this->renderer->setProject($project);

        $preferredLanguage = (!empty($widgetPage->getLanguage())) ? $widgetPage->getLanguage() : 'nl';

        $renderedWidgets = [
            'search_results' => $this->renderer->renderWidget($searchResultsWidget, '', $preferredLanguage),
            'facets' => [],
            'preferredLanguage' => $preferredLanguage,
        ];

        $searchResult = $searchResultsWidget->getSearchResult();
        foreach ($facetWidgets as $facetWidgetId => $facetWidget) {
            $facetWidget->setSearchResult($searchResult);
            $renderedWidgets['facets'][$facetWidgetId] = $this->renderer->renderWidget($facetWidget, '', $preferredLanguage);
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

        $project = $this->projectConverter->convert($widgetPage->getProjectId());
        if (!$project) {
            throw new NotFoundHttpException();
        }
        $this->renderer->setProject($project);

        $preferredLanguage = (!empty($widgetPage->getLanguage())) ? $widgetPage->getLanguage() : 'nl';

        $data = [
            'data' => $this->renderer->renderDetailPage($widget, $preferredLanguage),
        ];
        $response = new JsonResponse($data);

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
     * @param Request $request
     * @param $searchString
     * @param $language
     * @return JsonResponse
     */
    public function getRegionAutocompleteResult(Request $request, $searchString, $language = 'nl')
    {
        $matches = $this->regionService->getAutocompletResults($searchString, $language);
        // Sort $matches according levenshtein distance
        $matches = $this->regionService->sortByLevenshtein($matches, $searchString);

        // Return 10 matches
        $response = new JsonResponse(array_slice($matches, 0, 10));

        // If this is a jsonp request, set the requested callback.
        if ($request->query->has('callback')) {
            $response->setCallback($request->query->get('callback'));
        }

        return $response;
    }
}
