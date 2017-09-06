<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\QueueWidgetMigration;
use CultuurNet\ProjectAanvraag\Project\ProjectService;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use Doctrine\DBAL\Connection;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;


/**
 * Event listener for queuing old widget data for migration.
 */
class QueueWidgetMigrationEventListener
{

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
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * QueueWidgetMigrationEventListener constructor.
     * @param Connection $legacy_db
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $repository
     * @param MessageBusSupportingMiddleware $eventBus
     */
    public function __construct(Connection $legacy_db, EntityManagerInterface $entityManager, EntityRepository $repository, MessageBusSupportingMiddleware $eventBus)
    {
        $this->legacyDatabase = $legacy_db;
        $this->entityManager = $entityManager;
        $this->projectRepository = $repository;
        $this->eventBus = $eventBus;
    }

    /**
     * Handle the event
     * @param QueueWidgetMigration $event
     */
    public function handle(QueueWidgetMigration $event)
    {
        // Retrieve chunk of pages from legacy DB.
        $pageQueryBuilder = $this->legacyDatabase->createQueryBuilder();
        $results = $pageQueryBuilder
            ->select('pa.pid AS page_id', 'pa.layout', 'pa.name AS title', 'pr.name AS project', 'pr.description', 'pr.userpoolkey AS live_uid', 'pr.application_key AS live_consumer_key', 'pr.status', 'pa.created', 'pa.changed')
            ->from('cul_page', 'pa')
            ->leftJoin('pa', 'cul_project', 'pr', 'pa.project = pr.pid')
            ->setFirstResult($event->getStart())
            ->setMaxResults($event->getMax())
            ->execute()->fetchAll();

        foreach ($results as $key => $result) {
            // Retrieve blocks for the widget page.
            $blockQueryBuilder = $this->legacyDatabase->createQueryBuilder();
            $blocks = $blockQueryBuilder
                ->select('type', 'region', 'settings')
                ->from('cul_block')
                ->where('page = ?')
                ->setParameter(0, $result['page_id'])
                ->execute()->fetchAll();
            $results[$key]['blocks'] = $blocks;

            // Check if the project exists in our database.
            $project = $this->projectRepository->findOneBy(['liveConsumerKey' => $result['live_consumer_key']]);
            if (!$project) {
                // Create new project.
                $project = new Project();
                $project->setName($result['project']);
                $project->setDescription($result['description']);
                $project->setUserId($result['live_uid']);
                $project->setStatus('application_sent'); // TODO: determine correct status from retrieved status id.
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

            // Update project/widget pages.

            // Save to DB (entity manager)

        }

        $this->entityManager->flush();

        // As long as we get the maximum number of objects, add event to queue with next starting index.
        if (count($results) == $event->getMax()) {
            $event->setStart($event->getStart() + $event->getMax());
            $this->eventBus->handle($event);
        }
    }
}
