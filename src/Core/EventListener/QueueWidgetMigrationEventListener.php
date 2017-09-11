<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\QueueWidgetMigration;
use CultuurNet\ProjectAanvraag\Widget\Command\MigrateWidgetPage;
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
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

    /**
     * QueueWidgetMigrationEventListener constructor.
     * @param Connection $legacy_db
     * @param EntityManagerInterface $entityManager
     * @param EntityRepository $repository
     * @param MessageBusSupportingMiddleware $eventBus
     * @param MessageBusSupportingMiddleware $commandBus
     */
    public function __construct(Connection $legacy_db, EntityManagerInterface $entityManager, EntityRepository $repository, MessageBusSupportingMiddleware $eventBus, MessageBusSupportingMiddleware $commandBus)
    {
        $this->legacyDatabase = $legacy_db;
        $this->entityManager = $entityManager;
        $this->projectRepository = $repository;
        $this->eventBus = $eventBus;
        $this->commandBus = $commandBus;
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

            $this->commandBus->handle(new MigrateWidgetPage($result));

        }

        // As long as we get the maximum number of objects, add event to queue with next starting index.
        if (count($results) == $event->getMax()) {
            $event->setStart($event->getStart() + $event->getMax());
            $this->eventBus->handle($event);
        }
    }
}
