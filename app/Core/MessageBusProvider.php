<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\CommandHandler\CreateProjectCommandHandler;
use CultuurNet\ProjectAanvraag\Project\Controller\ProjectController;
use CultuurNet\ProjectAanvraag\Project\ProjectCreatedListener;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
use Metadata\MetadataFactory;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\NullLogger;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use SimpleBus\Asynchronous\Consumer\StandardSerializedEnvelopeConsumer;
use SimpleBus\Asynchronous\MessageBus\AlwaysPublishesMessages;
use SimpleBus\Asynchronous\MessageBus\PublishesUnhandledMessages;
use SimpleBus\Asynchronous\Properties\DelegatingAdditionalPropertiesResolver;
use SimpleBus\Asynchronous\Routing\EmptyRoutingKeyResolver;
use SimpleBus\JMSSerializerBridge\JMSSerializerObjectSerializer;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;
use SimpleBus\Message\Bus\Middleware\FinishesHandlingMessageBeforeHandlingNext;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\Message\CallableResolver\CallableCollection;
use SimpleBus\Message\CallableResolver\CallableMap;
use SimpleBus\Message\CallableResolver\ServiceLocatorAwareCallableResolver;
use SimpleBus\Message\Handler\DelegatesToMessageHandlerMiddleware;
use SimpleBus\Message\Handler\Resolver\NameBasedMessageHandlerResolver;
use SimpleBus\Message\Name\ClassBasedNameResolver;
use SimpleBus\Message\Subscriber\NotifiesMessageSubscribersMiddleware;
use SimpleBus\Message\Subscriber\Resolver\NameBasedMessageSubscriberResolver;
use SimpleBus\RabbitMQBundleBridge\RabbitMQMessageConsumer;
use SimpleBus\RabbitMQBundleBridge\RabbitMQPublisher;
use SimpleBus\Serialization\Envelope\DefaultEnvelopeFactory;
use SimpleBus\Serialization\Envelope\Serializer\StandardMessageInEnvelopeSerializer;
use SimpleBus\Serialization\NativeObjectSerializer;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides all services for the message bus.
 */
class MessageBusProvider implements ServiceProviderInterface
{

    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {

        $config = Yaml::parse(file_get_contents(__DIR__ . '/../config/messagebus.yml'));

        $pimple['envelope_serializer'] = function (Container $pimple) {

            AnnotationRegistry::registerAutoloadNamespace("JMS\Serializer\Annotation", __DIR__ . '/../../vendor/jms/serializer/src');

            $jmsSerializer = SerializerBuilder::create()
                ->addMetadataDir(SerializerMetadata::directory(), SerializerMetadata::namespacePrefix())
                ->setAnnotationReader(new AnnotationReader())
                ->build();

            $objectSerializer = new JMSSerializerObjectSerializer($jmsSerializer, 'json');
            return new StandardMessageInEnvelopeSerializer(new DefaultEnvelopeFactory(), $objectSerializer);
        } ;

        $pimple['event_bus'] = function (Container $pimple) use ($config) {
            $eventBus = new MessageBusSupportingMiddleware();
            $eventBus->appendMiddleware(new PublishesAsynchronousMessages($pimple['publisher']));

            $eventBus->appendMiddleware(
                new NotifiesMessageSubscribersMiddleware(
                    $this->getEventSubscriberResolver(isset($config['subscribers']['synchronous']) ? $config['subscribers']['synchronous'] : [], $pimple)
                )
            );
            return $eventBus;
        };

        $pimple['event_bus_consumer'] = function (Container $pimple) use ($config) {
            $eventBus = new MessageBusSupportingMiddleware();
            $eventBus->appendMiddleware(
                new NotifiesMessageSubscribersMiddleware(
                    $this->getEventSubscriberResolver(isset($config['subscribers']['asynchronous']) ? $config['subscribers']['asynchronous'] : [], $pimple)
                )
            );
            return $eventBus;
        };

        $pimple['command_bus'] = function (Container $pimple) use ($config) {
            $commandBus = new MessageBusSupportingMiddleware();

            $commandHandlers = [];
            foreach ($config['handlers'] as $handlerId => $handlerInfo) {
                $commandHandlers[$handlerInfo['command']] = $handlerId;
            }

            $commandHandlerMap = new CallableMap(
                $commandHandlers,
                new ServiceLocatorAwareCallableResolver($pimple['service_loader'])
            );

            $commandNameResolver = new ClassBasedNameResolver();
            $commandHandlerResolver = new NameBasedMessageHandlerResolver(
                $commandNameResolver,
                $commandHandlerMap
            );

            $commandBus->appendMiddleware(new FinishesHandlingMessageBeforeHandlingNext());
            $commandBus->appendMiddleware(
                new DelegatesToMessageHandlerMiddleware(
                    $commandHandlerResolver
                )
            );

            return $commandBus;
        };

        $pimple['publisher'] = function (Container $pimple) {

            $producer = new Producer($pimple['rabbit.connection']);
            $producer->setExchangeOptions(
                [
                    'name' => 'asynchronous_commands',
                    'type' => 'direct',
                ]
            );
            $producer->setQueueOptions(['name' => 'projectaanvraag', 'durable' => false]);
            $routingKeyResolver = new EmptyRoutingKeyResolver();
            $additionalPropertiesResolver = new DelegatingAdditionalPropertiesResolver([]);

            return new RabbitMQPublisher($pimple['envelope_serializer'], $producer, $routingKeyResolver, $additionalPropertiesResolver);
        };

        $pimple['rabbit.connection'] = function (Container $pimple) {
            $amqpConfig = $pimple['config']['rabbitmq'];
            return new AMQPStreamConnection($amqpConfig['host'], $amqpConfig['port'], $amqpConfig['user'], $amqpConfig['password']);
        };

        $pimple['rabbit.consumer'] = function (Container $pimple) {
            $envelopeConsumer = new StandardSerializedEnvelopeConsumer($pimple['envelope_serializer'], $pimple['event_bus_consumer']);
            return new RabbitMQMessageConsumer($envelopeConsumer, $pimple['dispatcher']);
        };

        // Register a service for every handler and listener.
        $this->registerServices($config['listeners'], $pimple);
        $this->registerServices($config['handlers'], $pimple);
    }

    /**
     * Get the subscriber resolver for given type.
     *
     * @param array $subscribers
     *   Subscribers to set.
     */
    private function getEventSubscriberResolver($subscribers, $pimple)
    {

        $eventSubscriberCollection = new CallableCollection(
            $subscribers,
            new ServiceLocatorAwareCallableResolver($pimple['service_loader'])
        );

        return new NameBasedMessageSubscriberResolver(
            new ClassBasedNameResolver(),
            $eventSubscriberCollection
        );
    }

    /**
     * Register services based on the given yml config.
     */
    private function registerServices($servicesToCreate, $pimple)
    {

        foreach ($servicesToCreate as $serviceId => $serviceProperties) {
            $pimple[$serviceId] = function (Container $pimple) use ($serviceProperties) {
                if (isset($serviceProperties['arguments'])) {
                    $arguments = [];
                    foreach ($serviceProperties['arguments'] as $argument) {
                        $arguments[] = $pimple[$argument];
                    }
                }

                $class = $serviceProperties['class'];
                return new $class(...$arguments);
            };
        }
    }
}
