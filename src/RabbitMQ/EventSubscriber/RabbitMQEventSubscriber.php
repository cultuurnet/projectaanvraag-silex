<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\EventSubscriber;

use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\RabbitMQBundleBridge\Event\Events;
use SimpleBus\RabbitMQBundleBridge\Event\MessageConsumptionFailed;
use SimpleBus\Serialization\Envelope\Envelope;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the RabbitMQ events.
 */
class RabbitMQEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var MessageInEnvelopSerializer
     */
    protected $messageInEnvelopeSerializer;

    /**
     * RabbitMQEventSubscriber constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param MessageInEnvelopSerializer $messageInEnvelopeSerializer
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, MessageInEnvelopSerializer $messageInEnvelopeSerializer)
    {
        $this->eventBus = $eventBus;
        $this->messageInEnvelopeSerializer = $messageInEnvelopeSerializer;
    }

    /**
     * Message consumption failed.
     * @param MessageConsumptionFailed $event
     */
    public function onConsumptionFailed(MessageConsumptionFailed $event)
    {
        /**
         * Unwrap the envelope and get the message
         * @var Envelope $envelope
         */
        $eventMessage = $event->message();
        $envelope = $this->messageInEnvelopeSerializer->unwrapAndDeserialize($eventMessage->body);
        $message = $envelope->message();

        // Only act on ProjectEvent events
        if ($message instanceof ProjectEvent) {
            /** @var ProjectEvent $message */
            $message->attempt();

            // @todo: Handle logging
            if ($message->getAttempts() < 5) {
                // Retry the command with delay
                $message->setDelay(5000);
                $this->eventBus->handle($message);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [Events::MESSAGE_CONSUMPTION_FAILED => 'onConsumptionFailed'];
    }
}
