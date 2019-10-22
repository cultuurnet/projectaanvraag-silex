<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageMigrated;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\Migration\FacetsWidgetWidgetMigration;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\Migration\CssMigration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Monolog\Logger;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to update a given widget page.
 */
class MigrateWidgetPageCommandHandler
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var Connection
     */
    protected $legacyDatabase;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    /**
     * @var WidgetPluginManager
     */
    protected $widgetLayoutManager;

    /**
     * MigrateWidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param Logger $logger
     * @param DocumentManager $documentManager
     * @param Connection $legacyDatabase
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $repository
     * @param WidgetPluginManager $widgetLayoutManager
     * @internal param Connection $legacy_db
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, Logger $logger, DocumentManager $documentManager, Connection $legacyDatabase, EntityManagerInterface $entityManager, EntityRepository $repository, WidgetPluginManager $widgetLayoutManager)
    {
        $this->eventBus = $eventBus;
        $this->logger = $logger;
        $this->documentManager = $documentManager;
        $this->legacyDatabase = $legacyDatabase;
        $this->entityManager = $entityManager;
        $this->projectRepository = $repository;
        $this->widgetLayoutManager = $widgetLayoutManager;
    }

    /**
     * Handle the command
     *
     * @param MigrateWidgetPage $data
     */
    public function handle(MigrateWidgetPage $data)
    {
        $result = $data->getResult();

        $existingPage = $this->documentManager->getRepository(WidgetPageEntity::class)->findOneBy(
            [
                'id' => $result['page_id'],
            ]
        );
        if ($existingPage) {
            $this->logger->info('Skipping ' . $result['page_id'] . ' as it already exists in db');
            return;
        }

        // Retrieve blocks for the widget page.
        $blockQueryBuilder = $this->legacyDatabase->createQueryBuilder();
        $blocks = $blockQueryBuilder
            ->select('bid AS id', 'type', 'region', 'settings')
            ->from('cul_block')
            ->where('page = ?')
            ->setParameter(0, $result['page_id'])
            ->execute()->fetchAll();
        $result['blocks'] = $blocks;

        // UTF8-encoding.
        $result['title'] = utf8_encode($result['title']);
        $result['description'] = utf8_encode($result['description']);

        // Check if the project exists in our database.
        $project = $this->projectRepository->findOneBy(['liveConsumerKey' => $result['live_consumer_key']]);
        if (!$project) {
            // Create new project.
            $project = new Project();
            $project->setName($result['project']);
            $project->setDescription($result['description']); // No database column for description.
            $project->setUserId($result['live_uid']);
            $project->setLiveConsumerKey($result['live_consumer_key']);

            // Set status.
            switch ($result['status']) {
                case '0':
                    $project->setStatus(ProjectInterface::PROJECT_STATUS_APPLICATION_SENT);
                    break;
                case '1':
                    $project->setStatus(ProjectInterface::PROJECT_STATUS_ACTIVE);
                    break;
                default:
                    $project->setStatus(ProjectInterface::PROJECT_STATUS_BLOCKED);
                    break;
            }

            // Set timestamps.
            $dtCreated = new \DateTime();
            $dtChanged = new \DateTime();
            $dtCreated->setTimestamp($result['created']);
            $dtChanged->setTimestamp($result['changed']);
            $project->setCreated($dtCreated);
            $project->setUpdated($dtChanged);

            // Persist project to MySQL database.
            $this->entityManager->persist($project);
            $this->entityManager->flush();

            $this->logger->info('Project did not exist yet: created new project for ' . $result['project']);
        }

        // Build widget page entity and persist to MongoDB database.
        $widgetPage = $this->serializeWidgetPage($result, $project);

        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();

        $this->logger->info('Migrated widget page ' . $result['page_id'] . ' - ' . $result['title']);

        // Dispatch the event.
        // $this->eventBus->handle(new WidgetPageMigrated($widgetPage));
    }

    /**
     * Convert the legacy widget page data to the proper v3 format.
     *
     * @param $data
     * @param $project
     * @return WidgetPageEntity
     */
    protected function serializeWidgetPage($data, $project)
    {
        $widgetPageEntity = new WidgetPageEntity();

        $widgetPageEntity->setVersion(2);
        $widgetPageEntity->setProjectId($project->getId());
        $widgetPageEntity->publish();

        if (isset($data['page_id'])) {
            $widgetPageEntity->setId($data['page_id']);
        }
        if (isset($data['title'])) {
            $widgetPageEntity->setTitle($data['title']);
        }

        if ($data['created']) {
            $widgetPageEntity->setCreated($data['created']);
        }
        if ($data['changed']) {
            $widgetPageEntity->setLastUpdated($data['changed']);
        }

        if (isset($data['blocks']) && !empty($data['blocks'])) {
            // Convert block and layout data to current version format.
            $rows = $this->convertBlocksToRows($data['layout'], $data['blocks']);
            $widgetPageEntity->setRows($rows);
            // Combine and set CSS.
            $cssMigration = new CssMigration($data['blocks']);
            $widgetPageEntity->setCss($cssMigration->getCss());
            $widgetPageEntity->setSelectedTheme($cssMigration->getSelectedTheme());
        }

        return $widgetPageEntity;
    }

    /**
     * Convert legacy blocks to proper v3 formatted rows.
     *
     * @param $layout
     * @param $blocks
     * @return array
     */
    protected function convertBlocksToRows($layout, $blocks)
    {
        $rows = [];
        $regionsMain = [];
        $regionsHeader = [];

        // Convert block data to widgets and add to correct regions array.
        foreach ($blocks as $i => $block) {
            $widgets = $this->convertBlockDataToWidgets($block);

            // Multiple widgets? Split it up in 2 regions.
            if (count($widgets) > 1) {
                $widget = $widgets['content'];

                // Create new region for the extra widget.
                if (isset($widgets['left'])) {
                    $layout = 'Cultuurnet_Widgets_Layout_ContentWithSidebarLayout';
                    $sidebarWidget = $widgets['left'];
                    $region = 'sidebar_left';
                } else {
                    $layout = 'Cultuurnet_Widgets_Layout_ContentWithRightSidebarLayout';
                    $sidebarWidget = $widgets['right'];
                    $region = 'sidebar_right';
                }

                $sidebarWidget['name'] = (count($blocks) == $i+1 ? $this->getWidgetName($sidebarWidget['type'], true) : $this->getWidgetName($sidebarWidget['type']));

                $regionsMain[$region]['widgets'][] = $sidebarWidget;
            } else {
                $widget = reset($widgets);
            }

            if ($widget) {
                $widget['name'] = (count($blocks) == $i+1 ? $this->getWidgetName($widget['type'], true) : $this->getWidgetName($widget['type']));
            }

            if ($widget && $block['region'] == 'header') {
                $regionsHeader['content']['widgets'][] = $widget;
            } elseif ($widget) {
                // We need to convert the region name to the corresponding v3 name.
                $regionsMain[$this->convertRegion($block['region'], $layout)]['widgets'][] = $widget;
            }
        }

        // If there are header regions: add header row
        // (we simulate old header layouts with an extra one-col row).
        if (!empty($regionsHeader)) {
            $rowHeader = [
                'type' => 'one-col',
                'regions' => $regionsHeader,
            ];
            $rows[] = $this->widgetLayoutManager->createInstance('one-col', $rowHeader, true);
        }

        // Add main content row (old page version only ever had a single row).
        $rowMain = [
            'type' => $this->convertType($layout),
            'regions' => $regionsMain,
        ];

        $rows[] = $this->widgetLayoutManager->createInstance($this->convertType($layout), $rowMain, true);

        return $rows;
    }

    /**
     * Convert legacy block data to corresponding formatted v3 widgets, including settings.
     *
     * @param $block
     * @return array
     */
    protected function convertBlockDataToWidgets($block)
    {
        // Build migration class name.
        $explType = explode('_', $block['type']);
        $type = array_pop($explType);
        $className = 'CultuurNet\ProjectAanvraag\Widget\Migration\\' . $type . 'Migration';
        if (class_exists($className)) {
            // Fix possible malformed serialized settings.
            $block['settings'] = utf8_encode($block['settings']);
            $block['settings'] = str_replace(["\r", "\n", "\t"], "", $block['settings']);
            // This fixes issues with CSS in settings messing with the character count (for the replace callback).
            $block['settings'] = preg_replace('/";(?!\w:|\}|N)/', '" ;', $block['settings']);
            // Fix character counts in serialized string.
            $block['settings'] = preg_replace_callback(
                '!s:(\d+):"(.*?)";!s',
                function ($m) {
                    $len = strlen($m[2]);
                    $result = "s:$len:\"{$m[2]}\";";
                    return $result;
                },
                $block['settings']
            );
            $settings = ($block['settings'] != null && $block['settings'] != '' ? unserialize($block['settings']) : []);

            // Special case for search results widget. Facets should be split in a new widget.
            if ($block['type'] === 'Cultuurnet_Widgets_Widget_SearchResultWidget') {
                if (isset($settings['control_results'], $settings['control_results']['refinements']) && count($settings['control_results']['refinements']['elements']) > 0) {
                    $facetMigration = new FacetsWidgetWidgetMigration($settings);
                    $widgets[$settings['control_results']['refinements']['position']] = [
                        'id' => $block['id'] . '-facet',
                        'type' => $facetMigration->getType(),
                        'settings' => $facetMigration->getSettings(),
                    ];

                    $widgets[$settings['control_results']['refinements']['position']]['settings']['search_results'] = $block['id'];
                }
            }

            $widgetMigration = new $className($settings);
            $widgets['content'] = [
                'id' => $block['id'],
                'type' => $widgetMigration->getType(),
                'settings' => $widgetMigration->getSettings(),
            ];
        } else {
            return [];
        }
        return $widgets;
    }

    /**
     * Convert a legacy page layout name to a corresponding row layout (for main content row).
     *
     * @param $layout
     * @return string
     */
    protected function convertType($layout)
    {
        switch ($layout) {
            case 'Cultuurnet_Widgets_Layout_SingleBoxLayout':
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderLayout':
                return 'one-col';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithSidebarLayout':
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderSidebarLayout':
                return '2col-sidebar-left';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithRightSidebarLayout':
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderRightSidebarLayout':
                return '2col-sidebar-right';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithTwoSidebarsLayout':
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderTwoSidebarsLayout':
                return '3col-double-sidebar';
                break;
            default:
                return 'one-col';
        }
    }

    /**
     * Convert a legacy region name to a corresponding v3 region name.
     *
     * @param $region
     * @return string
     */
    protected function convertRegion($region, $layout)
    {
        switch ($region) {
            case 'sidebar':
                return ($this->convertType($layout) === '2col-sidebar-left') ? 'sidebar_left' : 'sidebar_right';
                break;
            case 'main':
                return 'content';
            default:
                return $region;
        }
    }

    /**
     * Keep track of widget type counts and determine name accordingly.
     *
     * @param $type
     * @param bool $reset
     * @return string
     */
    protected function getWidgetName($type, $reset = false)
    {
        static $countHtml = 0;
        static $countSearchResults = 0;
        static $countSearchBox = 0;
        static $countTips = 0;

        $name = '';
        switch ($type) {
            case 'html':
                $countHtml++;
                $name = "html-$countHtml";
                break;
            case 'search-results':
                $countSearchResults++;
                $name = "search-results-$countSearchResults";
                break;
            case 'search-form':
                $countSearchBox++;
                $name = "search-form-$countSearchBox";
                break;
            case 'tips':
                $countTips++;
                $name = "tips-$countTips";
                break;
        }

        if ($reset) {
            $countHtml = 0;
            $countSearchResults = 0;
            $countSearchBox = 0;
            $countTips = 0;
        }

        return $name;
    }
}
