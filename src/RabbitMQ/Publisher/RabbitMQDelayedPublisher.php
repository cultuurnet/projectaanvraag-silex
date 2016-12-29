<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\Publisher;

use CultuurNet\ProjectAanvraag\RabbitMQ\DelayableMessageInterface;
use SimpleBus\RabbitMQBundleBridge\RabbitMQPublisher;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use OldSound\RabbitMqBundle\RabbitMq\Fallback;
use SimpleBus\Asynchronous\Properties\AdditionalPropertiesResolver;
use SimpleBus\Asynchronous\Routing\RoutingKeyResolver;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

class RabbitMQDelayedPublisher extends RabbitMQPublisher
{
    /**
     * @var MessageInEnvelopSerializer
     */
    private $serializer;

    /**
     * @var Producer|Fallback
     */
    private $producer;

    /**
     * @var RoutingKeyResolver
     */
    private $routingKeyResolver;

    /**
     * @var AdditionalPropertiesResolver
     */
    private $additionalPropertiesResolver;

    /**
     * RabbitMQDelayedPublisher constructor.
     * @param MessageInEnvelopSerializer $messageSerializer
     * @param $producer
     * @param RoutingKeyResolver $routingKeyResolver
     * @param AdditionalPropertiesResolver $additionalPropertiesResolver
     */
    public function __construct(
        MessageInEnvelopSerializer $messageSerializer,
        $producer,
        RoutingKeyResolver $routingKeyResolver,
        AdditionalPropertiesResolver $additionalPropertiesResolver
    ) {
        parent::__construct($messageSerializer, $producer, $routingKeyResolver, $additionalPropertiesResolver);

        $this->serializer = $messageSerializer;
        $this->producer = $producer;
        $this->routingKeyResolver = $routingKeyResolver;
        $this->additionalPropertiesResolver = $additionalPropertiesResolver;
    }

    /**
     * Publish the given Message by serializing it and handing it over to a RabbitMQ producer.
     *
     * @{inheritdoc}
     * @param object $message
     */
    public function publish($message)
    {
        $serializedMessage = $this->serializer->wrapAndSerialize($message);
        $routingKey = $this->routingKeyResolver->resolveRoutingKeyFor($message);
        $additionalProperties = $this->additionalPropertiesResolver->resolveAdditionalPropertiesFor($message);

        // Optional headers for delay, ...
        $headers = [];
        if ($message instanceof DelayableMessageInterface) {
            $headers += [
                'x-delay' => $message->getDelay(),
            ];
        }

        $this->producer->publish($serializedMessage, $routingKey, $additionalProperties, $headers);
    }
}
