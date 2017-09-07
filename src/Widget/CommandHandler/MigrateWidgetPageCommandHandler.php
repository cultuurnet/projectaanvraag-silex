<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageMigrated;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
use CultuurNet\ProjectAanvraag\Widget\WidgetPluginManager;
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
            ->select('type', 'region', 'settings')
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

            // Persist to database.
            $this->entityManager->persist($project);
        }

        $widgetPage = $this->serializeWidgetPage($result, $project);

        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();


        // TODO: do through event to flush at the end?
        $this->entityManager->flush();

        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPageMigrated($widgetPage));
    }

    protected function serializeWidgetPage($data, $project) {
        $widgetPageEntity = new WidgetPageEntity();

        if (isset($data['page_id'])) {
            $widgetPageEntity->setId($data['page_id']);
        }

        $widgetPageEntity->setVersion(2);


        if (isset($data['title'])) {
            $widgetPageEntity->setTitle($data['title']);
        }

        $widgetPageEntity->setProjectId($project->getId());

        if (isset($data['live_uid'])) {
            $widgetPageEntity->setCreatedBy($data['live_uid']);
        }

        /*
         * TODO: always the same as created by?
        if (isset($data['last_updated_by'])) {
            $widgetPageEntity->setLastUpdatedBy($data['last_updated_by']);
        }
        */


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
        }

        return $widgetPageEntity;
    }

    protected function convertBlocksToRows($layout, $blocks) {
        $rows = [];

        // TODO: need to check for ideal structure with minimal redundancy.
        switch ($layout) {
            case 'Cultuurnet_Widgets_Layout_SingleBoxLayout':
                $type = 'one-col';
                $widgets = [];
                foreach ($blocks as $block) {
                    $widgets[] = $this->convertBlockToWidget($block);
                }
                $row = [
                    'type' => $type,
                    'regions' => [
                        'content' => [
                            'widgets' => $widgets,
                        ],
                    ],
                ];
                // TODO - ERROR: Identifier "twig" is not defined.
                $rows[] = $this->widgetLayoutManager->createInstance($type, $row, true);
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithSidebarLayout':
                $type = '2col-sidebar-left';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithRightSidebarLayout':
                $type = '2col-sidebar-right';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithTwoSidebarsLayout':
                $type = '3col-double-sidebar';
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderLayout':
                // one col + one col
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderSidebarLayout':
                // one col + 2col-sidebar-left
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderRightSidebarLayout':
                // one col + 2col-sidebar-right
                break;
            case 'Cultuurnet_Widgets_Layout_ContentWithHeaderTwoSidebarsLayout':
                // one col + 3col-double-sidebar
                break;
        }

        return $rows;
    }

    protected function convertBlockToWidget($block) {
        $widget = [];
        $settings = unserialize($block['settings']);
        switch ($block['type']) {
            case 'Cultuurnet_Widgets_Widget_SearchBoxWidget':
                $widget = [
                    'name' => 'zoekformulier-1',
                    'type' => 'search-form',
                    'settings' => [],
                ];
                // header
                if (isset($settings['control_header']['html'])) {
                    $widget['settings']['header']['body'] = $settings['control_header']['html'];
                }
                // footer
                if (isset($settings['control_footer']['html'])) {
                    $widget['settings']['footer']['body'] = $settings['control_footer']['html'];
                }
                break;
            case 'Cultuurnet_Widgets_Widget_SearchResultWidget':
                $widget = [
                    'name' => 'search-results-1',
                    'type' => 'search-results',
                    'settings' => [],
                ];
                // header
                if (isset($settings['control_header']['html'])) {
                    $widget['settings']['header']['body'] = $settings['control_header']['html'];
                }
                // footer
                if (isset($settings['control_footer']['html'])) {
                    $widget['settings']['footer']['body'] = $settings['control_footer']['html'];
                }
                // current search
                if (isset($settings['control_results']['visual']['results']['current_search']['enabled'])) {
                    $widget['settings']['general']['current_search'] = $settings['control_results']['visual']['results']['current_search']['enabled'];
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
                break;
        }
        return $widget;
    }
}
