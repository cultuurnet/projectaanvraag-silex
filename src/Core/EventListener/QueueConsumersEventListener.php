<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\QueueConsumers;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Event listener for queuing consumers for synchronisation.
 */
class QueueConsumersEventListener
{
    /**
     * @var \ICultureFeed
     */
    protected $cultureFeed;

    /**
     * @var \ICultureFeed
     */
    protected $cultureFeedtest;

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
    public function __construct(\ICultureFeed $cultureFeed, \ICultureFeed $cultureFeedTest, MessageBusSupportingMiddleware $eventBus)
    {
        $this->cultureFeed = $cultureFeed;
        $this->cultureFeedtest = $cultureFeedTest;
        $this->eventBus = $eventBus;
    }

    /**
     * Handle the event
     * @param QueueConsumers $event
     */
    public function handle(QueueConsumers $event)
    {
        /** @var \CultureFeed_ResultSet $consumers */
        $consumers = null;

        if ($event->getType() == QueueConsumers::CONSUMER_TYPE_TEST) {
            $consumers = $this->cultureFeedtest->getServiceConsumers($event->getStart(), $event->getMax());
        }elseif ($event->getType() == QueueConsumers::CONSUMER_TYPE_LIVE) {
            $consumers = $this->cultureFeedtest->getServiceConsumers($event->getStart(), $event->getMax());
        }

        if ($consumers) {
            // As long as we get the maximum number of objects, requeue with next starting index
            if (!empty($consumers->objects) && count($consumers->objects) == $event->getMax()) {
                $event->setStart($event->getStart() + $event->getMax());
                $this->eventBus->handle($event);
            }
        }
    }
}
