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
            //$widgetPageEntity->setRows($rows);
        }

        return $widgetPageEntity;
    }

    protected function convertBlocksToRows($layout, $blocks) {
        $rows = [];
        //$rows[] = $this->widgetLayoutManager->createInstance('one-col', $row, true);
        return $rows;
    }
}
