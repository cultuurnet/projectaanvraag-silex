<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\RabbitMQ\Publisher\RabbitMQDelayedPublisher;
use CultuurNet\ProjectAanvraag\RabbitMQ\EventSubscriber\RabbitMQEventSubscriber;
use CultuurNet\ProjectAanvraag\RabbitMQ\RoutingKeyResolver\AsyncCommandRoutingKeyResolver;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\SerializerBuilder;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use SimpleBus\Asynchronous\Consumer\StandardSerializedEnvelopeConsumer;
use SimpleBus\Asynchronous\Properties\DelegatingAdditionalPropertiesResolver;
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
use SimpleBus\Serialization\Envelope\DefaultEnvelopeFactory;
use SimpleBus\Serialization\Envelope\Serializer\StandardMessageInEnvelopeSerializer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Provides all services for the message bus.
 */
class MessageBusProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    public function register(Container $pimple)
    {

        // Load the config.
        $config = [];
        if (file_exists($pimple['cache_directory'] . '/messagebus-config.php')) {
            $config = require $pimple['cache_directory'] . '/messagebus-config.php';
        } else {
            $config = Yaml::parse(file_get_contents(__DIR__ . '/../config/messagebus.yml'));
            file_put_contents($pimple['cache_directory'] . '/messagebus-config.php', '<?php return ' . var_export($config, true) . ';');
        }

        $pimple['envelope_serializer'] = function (Container $pimple) {
            $jmsSerializer = SerializerBuilder::create()
                ->addMetadataDir(SerializerMetadata::directory(), SerializerMetadata::namespacePrefix())
                ->setAnnotationReader(new AnnotationReader())
                ->build();
            $objectSerializer = new JMSSerializerObjectSerializer($jmsSerializer, 'json');
            return new StandardMessageInEnvelopeSerializer(new DefaultEnvelopeFactory(), $objectSerializer);
        };

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
            $disableDelay = $pimple['config']['rabbitmq']['disable_delay'] ?? false;
            if (!$disableDelay) {
                $producer->setExchangeOptions(
                    [
                        'declare' => true,
                        'name' => $pimple['config']['rabbitmq']['exchange'],
                        'type' => 'x-delayed-message',
                        'durable' => true,
                        'arguments' => new AMQPTable(
                            [
                                'x-delayed-type' => 'direct',
                            ]
                        ),
                    ]
                );
            } else {
                $producer->setExchangeOptions(
                    [
                        'declare' => true,
                        'name' => $pimple['config']['rabbitmq']['exchange'],
                        'type' => 'topic',
                        'durable' => true,
                    ]
                );
            }

            $producer->setQueueOptions(
                [
                    'declare' => true,
                    'name' => 'projectaanvraag',
                    'durable' => true,
                    'routing_keys' => [
                        'asynchronous_commands',
                    ],
                ]
            );

            // Declare delay queue.
            $channel = $producer->getChannel();

            $channel->queue_declare(
                'projectaanvraag_failed',
                false,
                true,
                false,
                false,
                false,
                new AMQPTable(
                    [
                        'routing_keys' => ['projectaanvraag_failed'],
                    ]
                )
            );

            $channel->queue_bind('projectaanvraag_failed', $pimple['config']['rabbitmq']['exchange'], 'projectaanvraag_failed');

            // Resolvers.
            $routingKeyResolver = new AsyncCommandRoutingKeyResolver();
            $additionalPropertiesResolver = new DelegatingAdditionalPropertiesResolver([]);

            return new RabbitMQDelayedPublisher($pimple['envelope_serializer'], $producer, $routingKeyResolver, $additionalPropertiesResolver);
        };

        $pimple['rabbit.connection'] = function (Container $pimple) {
            $amqpConfig = $pimple['config']['rabbitmq'];
            return new AMQPStreamConnection($amqpConfig['host'], $amqpConfig['port'], $amqpConfig['user'], $amqpConfig['password'], $amqpConfig['vhost']);
        };

        $pimple['rabbit.consumer'] = function (Container $pimple) {
            $envelopeConsumer = new StandardSerializedEnvelopeConsumer($pimple['envelope_serializer'], $pimple['event_bus_consumer']);
            return new RabbitMQMessageConsumer($envelopeConsumer, $pimple['dispatcher']);
        };

        // Logger service for rabbitmq
        $pimple['monolog.rabbitmq'] = function (Container $pimple) {
            $factory = $pimple['monolog.logger.class'];

            /** @var Logger $logger */
            $logger = new $factory('rabbitmq');

            $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/rabbitmq/log.log', 0, Logger::DEBUG));

            if ($pimple['debug']) {
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
            }

            return $logger;
        };

        // Logger service for projects
        $pimple['monolog.projects'] = function (Container $pimple) {
            $factory = $pimple['monolog.logger.class'];


            /** @var Logger $logger */
            $logger = new $factory('projects');

            $logger->pushHandler(new RotatingFileHandler(__DIR__ . '/../../log/projects/projects.log', 0, Logger::DEBUG));

            if ($pimple['debug']) {
                $logger->pushHandler(new BrowserConsoleHandler(Logger::DEBUG));
            }

            return $logger;
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
     * @param Container $pimple
     * @return NameBasedMessageSubscriberResolver
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
     * @param $servicesToCreate
     * @param $pimple
     */
    private function registerServices($servicesToCreate, $pimple)
    {

        foreach ($servicesToCreate as $serviceId => $serviceProperties) {
            $pimple[$serviceId] = function (Container $pimple) use ($serviceProperties) {
                $arguments = [];
                if (isset($serviceProperties['arguments'])) {
                    foreach ($serviceProperties['arguments'] as $argument) {
                        $arguments[] = $pimple[$argument];
                    }
                }

                $class = $serviceProperties['class'];
                return new $class(...$arguments);
            };
        }
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new RabbitMQEventSubscriber($app['event_bus'], $app['envelope_serializer'], $app['monolog.rabbitmq'], $app['monolog.projects'], $app['config']['rabbitmq']));
    }
}
