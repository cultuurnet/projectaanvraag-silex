<?php

namespace CultuurNet\ProjectAanvraag\EventSubscriber;

use SimpleBus\RabbitMQBundleBridge\Event\Events;
use SimpleBus\RabbitMQBundleBridge\Event\MessageConsumed;
use SimpleBus\RabbitMQBundleBridge\Event\MessageConsumptionFailed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the RabbitMQ events.
 */
class RabbitMQEventSubscriber implements EventSubscriberInterface
{
    /**
     * Message succesfully consumed.
     * @param MessageConsumed $event
     */
    public function onConsumed(MessageConsumed $event)
    {

    }

    /**
     * Message consumption failed.
     * @param MessageConsumptionFailed $event
     */
    public function onConsumptionFailed(MessageConsumptionFailed $event)
    {

    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MESSAGE_CONSUMED => 'onConsumed',
            Events::MESSAGE_CONSUMPTION_FAILED => 'onConsumptionFailed',
        ];
    }
}
