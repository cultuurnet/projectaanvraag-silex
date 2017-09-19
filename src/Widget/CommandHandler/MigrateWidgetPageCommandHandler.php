<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageMigrated;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\Migration\CssMigration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
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
     * @param DocumentManager $documentManager
     * @param Connection $legacy_db
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $repository
     * @param WidgetPluginManager $widgetLayoutManager
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, Connection $legacy_db, EntityManagerInterface $entityManager, EntityRepository $repository, WidgetPluginManager $widgetLayoutManager)
    {
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->legacyDatabase = $legacy_db;
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
            $project->setStatus(ProjectInterface::PROJECT_STATUS_ACTIVE); // TODO: determine correct status from retrieved status id.
            $project->setLiveConsumerKey($result['live_consumer_key']);

            // Set timestamps.
            $dtCreated = new \DateTime();
            $dtChanged = new \DateTime();
            $dtCreated->setTimestamp($result['created']);
            $dtChanged->setTimestamp($result['changed']);
            $project->setCreated($dtCreated);
            $project->setUpdated($dtChanged);

            // Persist project to MySQL database.
            $this->entityManager->persist($project);
        }

        // Build widget page entity and persist to MongoDB database.
        $widgetPage = $this->serializeWidgetPage($result, $project);
        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();

        $this->entityManager->flush();

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
    protected function serializeWidgetPage($data, $project) {
        $widgetPageEntity = new WidgetPageEntity();

        $widgetPageEntity->setVersion(2);
        $widgetPageEntity->setProjectId($project->getId());

        if (isset($data['page_id'])) {
            $widgetPageEntity->setId($data['page_id']);
        }
        if (isset($data['title'])) {
            $widgetPageEntity->setTitle($data['title']);
        }
        if (isset($data['live_uid'])) {
            $widgetPageEntity->setCreatedBy($data['live_uid']);
            $widgetPageEntity->setLastUpdatedBy($data['live_uid']);
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
    protected function convertBlocksToRows($layout, $blocks) {
        $rows = [];
        $regionsMain = [];
        $regionsHeader = [];

        // Convert block data to widgets and add to correct regions array.
        foreach ($blocks as $i => $block) {
            $widget = $this->convertBlockDataToWidget($block);

            $widget['name'] = (count($blocks) == $i+1 ? $this->getWidgetName($widget['type'], true) : $this->getWidgetName($widget['type']));

            if ($block['region'] == 'header') {
                $regionsHeader['content']['widgets'][] = $widget;
            }
            else {
                // We need to convert the region name to the corresponding v3 name.
                $regionsMain[$this->convertRegion($block['region'])]['widgets'][] = $widget;
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
    protected function convertBlockDataToWidget($block) {
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
            $block['settings'] = preg_replace_callback('!s:(\d+):"(.*?)";!s', function($m){
                $len = strlen($m[2]);
                $result = "s:$len:\"{$m[2]}\";";
                return $result;
            },
                $block['settings']
            );
            $settings = ($block['settings'] != null && $block['settings'] != '' ? unserialize($block['settings']) : []);

            $widgetMigration = new $className($settings);
            $widgetType = $widgetMigration->getType();
            $widget = [
                'id' => $block['id'],
                'type' => $widgetType,
                'settings' => $widgetMigration->getSettings(),
            ];
        }
        else {
            return [];
        }
        return $widget;
    }

    /**
     * Convert a legacy page layout name to a corresponding row layout (for main content row).
     *
     * @param $layout
     * @return string
     */
    protected function convertType($layout) {
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
    protected function convertRegion($region) {
        switch ($region) {
            case 'sidebar':
                return 'sidebar_left';
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
    protected function getWidgetName($type, $reset = false) {
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
