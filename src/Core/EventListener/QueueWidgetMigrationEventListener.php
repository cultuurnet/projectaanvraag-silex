<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\QueueWidgetMigration;
use Doctrine\DBAL\Connection;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

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
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * QueueConsumersEventListener constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     */
    public function __construct(Connection $legacy_db, MessageBusSupportingMiddleware $eventBus)
    {

        $this->legacyDatabase = $legacy_db;
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
            ->select('pa.pid AS page_id', 'pa.layout', 'pa.name AS title', 'pr.name AS project', 'pr.userpoolkey AS live_uid', 'pr.application_key AS live_consumer_key', 'pa.created', 'pa.changed')
            ->from('cul_page', 'pa')
            ->leftJoin('pa', 'cul_project', 'pr', 'pa.project = pr.pid')
            ->setFirstResult($event->getStart())
            ->setMaxResults($event->getMax())
            ->execute()->fetchAll();

        // Retrieve blocks for each page.
        foreach ($results as $key => $result) {
            $blockQueryBuilder = $this->legacyDatabase->createQueryBuilder();
            $blocks = $blockQueryBuilder
                ->select('type', 'region', 'settings')
                ->from('cul_block')
                ->where('page = ?')
                ->setParameter(0, $result['page_id'])
                ->execute()->fetchAll();
            $results[$key]['blocks'] = $blocks;
        }

        // As long as we get the maximum number of objects, add event to queue with next starting index.
        if (count($results) == $event->getMax()) {
            $event->setStart($event->getStart() + $event->getMax());
            $this->eventBus->handle($event);
        }
    }
}
