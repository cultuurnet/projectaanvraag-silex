<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageMigrated;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Widget\Entities\WidgetPageEntity;
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
     * MigrateWidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentManager $documentManager
     * @param Connection $legacy_db
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $repository
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, Connection $legacy_db, EntityManagerInterface $entityManager, EntityRepository $repository)
    {
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->legacyDatabase = $legacy_db;
        $this->entityManager = $entityManager;
        $this->projectRepository = $repository;
    }

    /**
     * Handle the command
     *
     * @param $data
     */
    public function handle($data)
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

        //$this->documentManager->persist($widgetPage);
        $this->documentManager->flush();


        // TODO: do through event to flush at the end?
        $this->entityManager->flush();

        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPageMigrated($widgetPage));
    }

    protected function serializeWidgetPage($data, $project) {
        $widgetPageEntity = new WidgetPageEntity();

        if (isset($data['pid'])) {
            $widgetPageEntity->setId($data['id']);
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

        if ($project->getCreated()) {
            $widgetPageEntity->setCreated($project->getCreated());
        }

        if ($project->getUpdated()) {
            $widgetPageEntity->setLastUpdated($project->getUpdated());
        }

        /*
        $rows = [];
        if (isset($data['rows']) && is_array($data['rows'])) {
            foreach ($data['rows'] as $row) {
                $rows[] = $this->widgetLayoutManager->createInstance($row['type'], $row, true);
            }
        }

        $widgetPageEntity->setRows($rows);
        */

        return $widgetPageEntity;
    }
}
