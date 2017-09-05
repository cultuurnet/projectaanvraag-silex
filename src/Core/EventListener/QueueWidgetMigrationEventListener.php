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
     * @param \ICultureFeed $cultureFeed
     * @param \ICultureFeed $cultureFeedTest
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
        //$this->legacyDatabase->query();
        var_dump('FOO');

        /** @var \CultureFeed_ResultSet $consumers */
        /*$consumers = null;
        $type = $event->getType();

        if ($type == ConsumerTypeInterface::CONSUMER_TYPE_TEST) {
            $consumers = $this->cultureFeedtest->getServiceConsumers($event->getStart(), $event->getMax());
        } elseif ($type == ConsumerTypeInterface::CONSUMER_TYPE_LIVE) {
            $consumers = $this->cultureFeedtest->getServiceConsumers($event->getStart(), $event->getMax());
        }

        if (!empty($consumers->objects)) {
            // As long as we get the maximum number of objects, add event to queue with next starting index
            if (count($consumers->objects) == $event->getMax()) {
                $event->setStart($event->getStart() + $event->getMax());
                $this->eventBus->handle($event);
            }

            // Create sync commands
            foreach ($consumers->objects as $object) {
                $this->eventBus->handle(new SyncConsumer($type, $object));
            }
        }*/
    }
}
