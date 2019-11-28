<?php

namespace CultuurNet\ProjectAanvraag\Widget;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use CultuurNet\ProjectAanvraag\Widget\WidgetLayout\OneCol;
use CultuurNet\ProjectAanvraag\Widget\WidgetLayout\TwoColSidebarLeft;
use CultuurNet\ProjectAanvraag\Widget\WidgetLayout\TwoColSidebarRight;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\Facets;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\Html;
use CultuurNet\ProjectAanvraag\Widget\WidgetType\SearchResults;
use CultuurNet\SearchV3\SearchClient;
use CultuurNet\SearchV3\SearchClientInterface;
use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use Doctrine\ORM\EntityRepository;

/**
 * The rendered used to render widget pages or widget details in javascript.
 */
class Renderer implements RendererInterface
{

    /**
     * @var WidgetPluginManager
     */
    protected $widgetPluginManager;

    /**
     * Current active project.
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var string
     */
    protected $googleTagManagerId;

    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    /**
     * @var SearchClientInterface
     */
    protected $searchClient;

    /**
     * @var SearchClientInterface
     */
    protected $searchClientTest;

    /**
     * @var CuratorenClient
     */
    protected $curatorenClient;

    /**
     * @var CuratorenClient
     */
    protected $curatorenClientTest;

    /**
     * @var array
     */
    private $jsFiles = [];

    /**
     * @var array
     */
    private $cssFiles = [];

    /**
     * @var array
     */
    private $settings = [];

    /**
     * Renderer constructor.
     * @param WidgetPluginManager $widgetPluginManager
     * @param $googleTagManagerId
     * @param ProjectServiceInterface $projectService
     */
    public function __construct(WidgetPluginManager $widgetPluginManager, $googleTagManagerId, EntityRepository $projectRepository, SearchClientInterface $searchClient, SearchClientInterface $searchClientTest, CuratorenClient $curatorenClient, CuratorenClient $curatorenClientTest)
    {
        $this->widgetPluginManager = $widgetPluginManager;
        $this->googleTagManagerId = $googleTagManagerId;
        $this->projectRepository = $projectRepository;
        $this->searchClient = $searchClient;
        $this->searchClientTest = $searchClientTest;
        $this->curatorenClient = $curatorenClient;
        $this->curatorenClientTest = $curatorenClientTest;
    }

    /**
     * @inheritDoc
     */
    public function addSettings(array $settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    /**
     * @inheritDoc
     */
    public function setProject(ProjectInterface $project)
    {
        $this->project = $project;

        // If a project is not live yet. We should use the test api + test key.
        if ($project->getStatus() !== ProjectInterface::PROJECT_STATUS_ACTIVE) {
            $apiKey = $project->getTestApiKeySapi3();
            $config = $this->searchClientTest->getClient()->getConfig();
            $curatorenConfig =  $this->curatorenClientTest->getClient()->getConfig();
        } else {
            $config = $this->searchClient->getClient()->getConfig();
            $apiKey = $project->getLiveApiKeySapi3();
            $curatorenConfig =  $this->curatorenClient->getClient()->getConfig();
        }

        $headers = $config['headers'] ?? [];
        $headers['X-Api-Key'] = $apiKey;
        $config['headers'] = $headers;

        $this->searchClient->setClient(new \GuzzleHttp\Client($config));
        $this->curatorenClient->setClient(new \GuzzleHttp\Client($curatorenConfig));
    }

    /**
     * @inheritDoc
     */
    public function renderPage(WidgetPageInterface $widgetPage)
    {

        $criteria = [
            'id' => $widgetPage->getProjectId(),
        ];

        /** @var Project $project */
        $this->setProject($this->projectRepository->findOneBy($criteria));

        $this->attachCss(WWW_ROOT . '/assets/css/cn_widget_styling.css');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/widgets.js');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/settings-loader.js');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/placeholder-load.js');
        $this->attachJavascript(WWW_ROOT . '/assets/js/widgets/core/tracking.js');

        $widgetMapping = [];
        $rows = $widgetPage->getRows();
        $searchResultWidget = null;
        $searchResultWidgetRow = null;
        $searchResultWidgetRowIndex = null;
        $facetsInRows = [];
        $htmlInRows = [];
        $rowOutput = [];
        foreach ($rows as $i => $row) {
            $widgetIds = $row->getWidgetIds();
            foreach ($widgetIds as $widgetId) {
                $widget = $row->getWidget($widgetId);
                if (!$searchResultWidget && $widget instanceof SearchResults) {
                    $searchResultWidget = $widget;
                    $searchResultWidgetRow = $row;
                    $searchResultWidgetRowIndex = $i;
                }

                if ($widget instanceof Facets) {
                    $facetsInRows[$i][] = $widget;
                }

                if ($widget instanceof Html) {
                    $htmlInRows[$i][] = $widget;
                }

                $widgetMapping[$widgetId] = $widgetPage->getId();
            }

            $rowOutput[$i] = $row->render();
        }


        $this->addSettings(['widgetPageRows' => $rowOutput]);
        $this->addSettings(['widgetPageId' => $widgetPage->getId()]);

        // If there is a search results widget, include the detail page version also.
        if ($searchResultWidget) {
            $detailPageRow = $this->getDetailPageRow($searchResultWidgetRow, $searchResultWidgetRowIndex, $facetsInRows, $htmlInRows);
            $this->addSettings(['detailPage' => $detailPageRow->render()]);
            $this->addSettings(['detailPageRowId' => $searchResultWidgetRowIndex]);
            $this->addSettings(['detailPageWidgetId' => $searchResultWidget->getId()]);
        }

        $this->addSettings(['widgetMapping' => $widgetMapping]);

        // Add settings for google tag manager.
        $this->addSettings(
            [
            'googleTagManagerId' => $this->googleTagManagerId,
            'widgetPageTitle' => $widgetPage->getTitle(),
            'consumerKey' => $this->project->getLiveConsumerKey(),
            'consumerName' => $this->project->getName(),
            'mobile' => $widgetPage->getMobile(),
            'jquery' => $widgetPage->getJquery(),
            'language' => $widgetPage->getLanguage(),
            ]
        );

        $this->attachJavascript('CultuurnetWidgets.loadSettings(' . json_encode($this->settings) . ')', 'inline');

        return '<div class="cultuurnet-widgets" data-widget-page-id="' . $widgetPage->getId() . '"></div>';
    }

    /**
     * @inheritDoc
     */
    public function renderWidget(WidgetTypeInterface $widgetType, $cdbid = '', string $preferredLanguage = 'nl')
    {
        return $widgetType->render($cdbid, $preferredLanguage);
    }

    public function renderDetailPage(SearchResults $searchResultsWidget, string $preferredLanguage = 'nl')
    {
        return $searchResultsWidget->renderDetail($preferredLanguage);
    }

    /**
     * @inheritDoc
     */
    public function attachJavascript($value, $type = 'file', $weight = 0)
    {
        $this->jsFiles[] = [
            'value' => $value,
            'type' => $type,
            'weight' => $weight,
        ];
    }

    /**
     * @inheritDoc
     */
    public function attachCss($path, $weight = 0)
    {
        $this->cssFiles[] = [
            'path' => $path,
            'weight' => $weight,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAttachedJs()
    {
        uasort($this->jsFiles, [$this, 'sortByWeight']);
        return $this->jsFiles;
    }

    /**
     * @inheritDoc
     */
    public function getAttachedCss()
    {
        uasort($this->cssFiles, [$this, 'sortByWeight']);
        return $this->cssFiles;
    }

    /**
     * Sort the array by weight.
     */
    public function sortByWeight($a, $b)
    {

        $aWeight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
        $bWeight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;
        if ($aWeight == $bWeight) {
            return 0;
        }
        return ($aWeight < $bWeight) ? -1 : 1;
    }

    /**
     * Get a cleaned up row for a detail page.
     * All facets should get removed, and empty regions after removal should be merged to a new row layout.
     */
    protected function getDetailPageRow(LayoutInterface $sourceRow, $sourceRowIndex, $facetsInRows, $htmlInRows)
    {
        $detailPageRow = $sourceRow;

        // If the row also has facets, we need to remove them, and merge regions if needed.
        if (isset($facetsInRows[$sourceRowIndex])) {
            // If the row has html widgets, only remove them if the region also has a facet widget.
            if (isset($htmlInRows[$sourceRowIndex])) {
                foreach ($htmlInRows[$sourceRowIndex] as $widgetToRemove) {
                    $sourceRow->removeWidget($widgetToRemove);
                }
            }

            // Remove all the facets.
            foreach ($facetsInRows[$sourceRowIndex] as $widgetToRemove) {
                $sourceRow->removeWidget($widgetToRemove);
            }

            // Check if regions have empty regions after the cleanup. If so, merge them.

            // Rows with 2 regions. Merge to 1 col if needed.
            if ($sourceRow instanceof TwoColSidebarLeft || $sourceRow instanceof TwoColSidebarRight) {
                $sidebarRegion = $sourceRow instanceof TwoColSidebarRight ? 'sidebar_right' : 'sidebar_left';
                if ($sourceRow->isRegionEmpty('content') || $sourceRow->isRegionEmpty($sidebarRegion)) {
                    $detailPageRow = $this->widgetPluginManager->createInstance('one-col');
                    $widgetIds = $sourceRow->getWidgetIds();
                    foreach ($widgetIds as $widgetId) {
                        $detailPageRow->addWidget('content', $sourceRow->getWidget($widgetId));
                    }
                }
            } elseif ($sourceRow->isRegionEmpty('content') || $sourceRow->isRegionEmpty('sidebar_right') || $sourceRow->isRegionEmpty('sidebar_right')) {
                // Rows with 3 regions. Merge to 1col or 2 col.

                // Only 1 region left? Create a one col.
                if ($sourceRow->isRegionEmpty('sidebar_right') && $sourceRow->isRegionEmpty('sidebar_left')) {
                    $detailPageRow = $this->widgetPluginManager->createInstance('one-col');
                    $widgetIds = $sourceRow->getWidgetIds();
                    foreach ($widgetIds as $widgetId) {
                        $detailPageRow->addWidget('content', $sourceRow->getWidget($widgetId));
                    }
                } else {
                    $sidebarRegion = '';
                    // Create a 2col if one of the regions was not empty.
                    if ($sourceRow->isRegionEmpty('sidebar_right')) {
                        $detailPageRow = $this->widgetPluginManager->createInstance('2col-sidebar-left');
                        $sidebarRegion = 'sidebar_left';
                    } else {
                        $detailPageRow = $this->widgetPluginManager->createInstance('2col-sidebar-right');
                        $sidebarRegion = 'sidebar_right';
                    }

                    $widgetIds = $sourceRow->getWidgetIds();
                    $widgetMapping = $sourceRow->getWidgetMapping();
                    foreach ($widgetIds as $widgetId) {
                        $region = $widgetMapping[$widgetId] == 'content' ? 'content' : $sidebarRegion;
                        $detailPageRow->addWidget($region, $sourceRow->getWidget($widgetId));
                    }
                }
            }
        }

        return $detailPageRow;
    }
}
