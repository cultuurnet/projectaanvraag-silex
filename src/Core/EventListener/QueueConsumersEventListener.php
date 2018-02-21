<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\ConsumerTypeInterface;
use CultuurNet\ProjectAanvraag\Core\Event\QueueConsumers;
use CultuurNet\ProjectAanvraag\Core\Event\SyncConsumer;
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
        $type = $event->getType();

        $groupIds = ['5', '3'];
        foreach($groupIds as $groupId) {
          $filter = ['showChannelAdmins' => 'true', 'groupId' => '5', 'showChannelGroups' => 'true'];

          if ($type == ConsumerTypeInterface::CONSUMER_TYPE_TEST) {
              $consumers = $this->cultureFeedtest->getServiceConsumers($event->getStart(), $event->getMax(), $filter);
          } elseif ($type == ConsumerTypeInterface::CONSUMER_TYPE_LIVE) {
              $consumers = $this->cultureFeed->getServiceConsumers($event->getStart(), $event->getMax(), $filter);
          }

          if (!empty($consumers->objects)) {
              // As long as we get the maximum number of objects, add event to queue with next starting index.
              if (count($consumers->objects) == $event->getMax()) {
                  $event->setStart($event->getStart() + $event->getMax());
                  $this->eventBus->handle($event);
              }

              // Create sync commands
              foreach ($consumers->objects as $object) {
                  $this->eventBus->handle(new SyncConsumer($type, $object));
              }
          }
        }
    }
}
