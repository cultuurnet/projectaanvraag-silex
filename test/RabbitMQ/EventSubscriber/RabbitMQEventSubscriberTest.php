<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\EventSubscriber;

use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\Project\Event\AbstractProjectEvent;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use SimpleBus\RabbitMQBundleBridge\Event\Events;
use SimpleBus\RabbitMQBundleBridge\Event\MessageConsumptionFailed;
use SimpleBus\Serialization\Envelope\Envelope;
use SimpleBus\Serialization\Envelope\Serializer\MessageInEnvelopSerializer;

class RabbitMQEventSubscriberTest extends TestCase
{
    /**
     * @var MessageBusSupportingMiddleware & MockObject
     */
    protected $eventBus;

    /**
     * @var MessageInEnvelopSerializer & MockObject
     */
    protected $messageInEnveloppeSerializer;

    /**
     * @var LoggerInterface & MockObject
     */
    protected $logger;

    /**
     * @var LoggerInterface & MockObject
     */
    protected $projectLogger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AbstractProjectEvent & MockObject
     */
    protected $projectEvent;

    /**
     * @var MessageConsumptionFailed & MockObject
     */
    protected $messageConsumptionFailedEvent;

    /**
     * @var Envelope & MockObject
     */
    protected $envelope;

    /**
     * @var AMQPMessage & MockObject
     */
    protected $amqpMessage;

    /**
     * @var \Exception & MockObject
     */
    protected $exception;

    public function setUp()
    {
        // Config
        $this->config['failed_message_delay'] = 5000;

        // Event bus
        $this->eventBus = $this->createMock(MessageBusSupportingMiddleware::class);

        // Logger
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->projectLogger = $this->createMock(LoggerInterface::class);

        // ProjectEvent
        $this->projectEvent = $this->createMock(ProjectCreated::class);

        // Amqpmessage
        $this->amqpMessage = $this->createMock(AMQPMessage::class);
        $this->amqpMessage->body = 'message';

        // Message failed event
        $this->messageConsumptionFailedEvent = $this->createMock(MessageConsumptionFailed::class);

        $this->exception = $this->createMock(\Exception::class);

        $this->envelope = $this->createMock(Envelope::class);

        // MessageInEvenloppeSerializer
        $this->messageInEnveloppeSerializer = $this->createMock(MessageInEnvelopSerializer::class);

        $this->messageInEnveloppeSerializer->expects($this->any())
            ->method('unwrapAndDeserialize')
            ->with('message')
            ->willReturn($this->envelope);
    }

    /**
     * Test the onConsumptionFailed handler
     */
    public function testOnConsumptionFailed()
    {
        $this->eventBus->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(AbstractProjectEvent::class));

        $this->projectEvent->expects($this->once())
            ->method('attempt');

        $this->projectEvent->expects($this->once())
            ->method('getAttempts')
            ->willReturn(3);

        $this->projectEvent->expects($this->once())
            ->method('setDelay')
            ->with(5000);

        $this->envelope->expects($this->once())
            ->method('message')
            ->willReturn($this->projectEvent);

        $this->logger->expects($this->once())
            ->method('error');

        $this->messageConsumptionFailedEvent->expects($this->at(0))
            ->method('message')
            ->willReturn($this->amqpMessage);

        $this->messageConsumptionFailedEvent->expects($this->at(1))
            ->method('exception')
            ->willReturn($this->exception);

        // Trigger the event
        $this->triggerOnconsumptionFailed();
    }

    /**
     * Test the onConsumptionFailed handler when too many attempts have happened and the message gets logged
     */
    public function testOnConsumptionFailedAndLogged()
    {
        $this->projectEvent->setAttempts(5);

        $this->projectEvent->expects($this->once())
            ->method('attempt');

        $this->projectEvent->expects($this->once())
            ->method('getAttempts')
            ->willReturn(5);

        $this->envelope->expects($this->once())
            ->method('message')
            ->willReturn($this->projectEvent);

        $this->logger->expects($this->once())
            ->method('error');

        $this->projectLogger->expects($this->once())
            ->method('error')
            ->with('Message: ' . 'message');

        $this->messageConsumptionFailedEvent->expects($this->at(0))
            ->method('message')
            ->willReturn($this->amqpMessage);

        $this->messageConsumptionFailedEvent->expects($this->at(1))
            ->method('exception')
            ->willReturn($this->exception);

        // Trigger the event
        $this->triggerOnconsumptionFailed();
    }

    /**
     * Test the subscribed events
     */
    public function testSubscribedEvents()
    {
        $subscribedEvents = RabbitMQEventSubscriber::getSubscribedEvents();
        $this->assertEquals([Events::MESSAGE_CONSUMPTION_FAILED => 'onConsumptionFailed'], $subscribedEvents, 'It correctly returns the subscribed events');
    }

    /**
     * Helper function for triggering the event on the envent subscriber
     */
    private function triggerOnconsumptionFailed()
    {
        $rabbitMQEventSubscriber = new RabbitMQEventSubscriber($this->eventBus, $this->messageInEnveloppeSerializer, $this->logger, $this->projectLogger, $this->config);
        $rabbitMQEventSubscriber->onConsumptionFailed($this->messageConsumptionFailedEvent);
    }
}
