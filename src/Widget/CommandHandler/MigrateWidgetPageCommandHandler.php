<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageMigrated;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
use CultuurNet\ProjectAanvraag\Widget\Migration;
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
            $dt_created = new \DateTime();
            $dt_changed = new \DateTime();
            $dt_created->setTimestamp($result['created']);
            $dt_changed->setTimestamp($result['changed']);
            $project->setCreated($dt_created);
            $project->setUpdated($dt_changed);

            // Persist project to MySQL database.
            $this->entityManager->persist($project);
        }

        // Build widget page entity and persist to MongoDB database.
        $widgetPage = $this->serializeWidgetPage($result, $project);
        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();

        // TODO: do through event to flush at the end?
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
        }

        if (isset($data['last_updated_by'])) {
            $widgetPageEntity->setLastUpdatedBy($data['live_uid']);
        }

        if ($data['created']) {
            $widgetPageEntity->setCreated($data['created']);
        }
        if ($data['changed']) {
            $widgetPageEntity->setLastUpdated($data['changed']);
        }

        if (isset($data['blocks'])) {
            // Convert block and layout data to current version format.
            $rows = $this->convertBlocksToRows($data['layout'], $data['blocks']);
            $widgetPageEntity->setRows($rows);
            // Combine and set CSS.
            $css = $this->combineCssRecursively($data['blocks']);
            $widgetPageEntity->setCss($css);
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
        $regions_main = [];
        $regions_header = [];

        // Convert block data to widgets and add to correct regions array.
        foreach ($blocks as $block) {
            $widgets = [];
            $widgets[] = $this->convertBlockDataToWidget($block);

            if ($block['region'] == 'header') {
                $regions_header['content']['widgets'] = $widgets;
            }
            else {
                // We need to convert the region name to the corresponding v3 name.
                $regions_main[$this->convertRegion($block['region'])]['widgets'] = $widgets;
            }
        }

        // If there are header regions: add header row
        // (we simulate old header layouts with an extra one-col row).
        if (!empty($regions_header)) {
            $row_header = [
                'type' => 'one-col',
                'regions' => $regions_header,
            ];
            $rows[] = $this->widgetLayoutManager->createInstance('one-col', $row_header, true);
        }

        // Add main content row (old page version only ever had a single row).
        $row_main = [
            'type' => $this->convertType($layout),
            'regions' => $regions_main,
        ];
        $rows[] = $this->widgetLayoutManager->createInstance($this->convertType($layout), $row_main, true);
        return $rows;
    }

    /**
     * Convert legacy block data to corresponding formatted v3 widgets, including settings.
     * TODO: WIP
     *
     * @param $block
     * @return array
     */
    protected function convertBlockDataToWidget($block) {
        // Build migration class name.
        $type = array_pop(explode('_', $block['type']));
        $className = 'CultuurNet\ProjectAanvraag\Widget\Migration\\' . $type . 'Migration';
        if (class_exists($className)) {
            $settings = unserialize($block['settings']);
            $widgetMigration = new $className($settings);
            $widget = [
                'id' => $block['id'],
                'name' => $widgetMigration->getName(),
                'type' => $widgetMigration->getType(),
                'settings' => $widgetMigration->getSettings(),
            ];
        }
        else {
            return [];
        }

        /*
        switch ($block['type']) {
            case 'Cultuurnet_Widgets_Widget_SearchBoxWidget':

                // what
                if (isset($settings['control_what']['fields'])) {
                    // what enabled
                    $widget['settings']['fields']['type']['keyword_search']['enabled'] = $settings['control_what']['fields']['q']['enabled'];
                    // what label
                    $widget['settings']['fields']['type']['keyword_search']['label'] = $settings['control_what']['fields']['q']['label'];
                    // what placeholder
                    $widget['settings']['fields']['type']['keyword_search']['placeholder'] = $settings['control_what']['fields']['q']['placeholder'];
                }
                // where
                if (isset($settings['control_where']['fields'])) {
                    // where enabled
                    $widget['settings']['fields']['location']['keyword_search']['enabled'] = $settings['control_where']['fields']['location']['enabled'];
                    // where label
                    $widget['settings']['fields']['location']['keyword_search']['label'] = $settings['control_where']['fields']['location']['label'];
                }
                // when
                if (isset($settings['control_when']['fields'])) {
                    // when enabled
                    $widget['settings']['fields']['time']['date_search']['enabled'] = $settings['control_when']['fields']['datetype']['enabled'];
                    // when label
                    $widget['settings']['fields']['time']['date_search']['label'] = $settings['control_when']['fields']['datetype']['label'];
                }
                // url
                if (isset($settings['url'])) {
                    $widget['settings']['general']['destination'] = $settings['url'];
                }
                // open in new window
                if (isset($settings['new_window'])) {
                    $widget['settings']['general']['new_window'] = $settings['new_window'];
                }
                break;
            case 'Cultuurnet_Widgets_Widget_SearchResultWidget':
                // current search
                if (isset($settings['control_results']['visual']['results']['current_search']['enabled'])) {
                    $widget['settings']['general']['current_search'] = $settings['control_results']['visual']['results']['current_search']['enabled'];
                }
                // items character limit
                if (isset($settings['control_results']['visual']['results']['char_limit'])){
                    $widget['settings']['items']['description']['characters'] = $settings['control_results']['visual']['results']['char_limit'];
                }
                // items image
                if (isset($settings['control_results']['visual']['results']['image'])) {
                    $img_settings = $settings['control_results']['visual']['results']['image'];
                    $widget['settings']['items']['image'] = [
                        'enabled' => $img_settings['show'],
                        'width' => $img_settings['size']['width'],
                        'height' => $img_settings['size']['height'],
                        'default_image' => $img_settings['show_default'],
                        'position' => 'left',
                    ];
                }
                // items icon vlieg
                if (isset($settings['control_results']['visual']['results']['logo_vlieg']['show'])) {
                    $widget['settings']['items']['icon_vlieg'] = $settings['control_results']['visual']['results']['logo_vlieg']['show'];
                }
                // detail map
                if (isset($settings['control_results']['visual']['detail']['map'])) {
                    $widget['settings']['detail_page']['map'] = $settings['control_results']['visual']['detail']['map']['show'];
                }
                // detail image
                if (isset($settings['control_results']['visual']['detail']['image'])) {
                    $img_settings = $settings['control_results']['visual']['detail']['image'];
                    $widget['settings']['detail_page']['image'] = [
                        'enabled' => $img_settings['show'],
                        'width' => $img_settings['size']['width'],
                        'height' => $img_settings['size']['height'],
                        'default_image' => false,
                        'position' => 'left',
                    ];
                }
                // detail icon vlieg
                if (isset($settings['control_results']['visual']['detail']['logo_vlieg']['show'])) {
                    $widget['settings']['detail_page']['icon_vlieg'] = $settings['control_results']['visual']['detail']['logo_vlieg']['show'];
                }
                // detail language icons
                if (isset($settings['control_results']['visual']['detail']['taaliconen']['show'])) {
                    $widget['settings']['detail_page']['language_icons'] = $settings['control_results']['visual']['detail']['taaliconen']['show'];
                }
                break;
            case 'Cultuurnet_Widgets_Widget_PushWidget':
                // items amount
                if (isset($settings['content']['count'])) {
                    $widget['settings']['general']['items'] = $settings['content']['count'];
                }
                // items image
                if (isset($settings['visual']['image'])) {
                    $img_settings = $settings['visual']['image'];
                    $widget['settings']['items']['image'] = [
                        'enabled' => $img_settings['show'],
                        'width' => $img_settings['size']['width'],
                        'height' => $img_settings['size']['height'],
                        'default_image' => $img_settings['show_default'] ?? false,
                        'position' => 'left',
                    ];
                }
                // where
                if (isset($settings['visual']['fields']['location'])) {
                    $widget['settings']['items']['where']['enabled'] = $settings['visual']['fields']['location'];
                }
                // age
                if (isset($settings['visual']['fields']['agefrom'])) {
                    $widget['settings']['items']['age']['enabled'] = $settings['visual']['fields']['agefrom'];
                }
                // read more
                if (isset($settings['visual']['fields']['readmore'])) {
                    $widget['settings']['items']['read_more']['enabled'] = $settings['visual']['fields']['readmore'];
                }
                break;
        }*/
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
     * Combine the CSS strings from every block recursively into a single string.
     *
     * @param $blocks
     * @return string
     */
    protected function combineCssRecursively($blocks) {
        global $css;
        foreach ($blocks as $block) {
            $settings = unserialize($block['settings']);

            // Recursively retrieve "css" key values.
            $callback = function(&$value, $key) {
                global $css;
                if ($key == 'css') {
                    if ($css != '') {
                        $css .= '\n';
                    }
                    $css .= "$value";
                }
            };
            array_walk_recursive($settings, $callback);
        }
        return $css;
    }
}
