<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\EventSubscriber;

use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;
use PhpAmqpLib\Message\AMQPMessage;
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
     * @var MessageBusSupportingMiddleware|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventBus;

    /**
     * @var MessageInEnvelopSerializer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageInEnveloppeSerializer;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectLogger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ProjectEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectEvent;

    /**
     * @var MessageConsumptionFailed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageConsumptionFailedEvent;

    /**
     * @var Envelope|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $envelope;

    /**
     * @var AMQPMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amqpMessage;

    /**
     * @var \Exception|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $exception;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        // Config
        $this->config['failed_message_delay'] = 5000;

        // Event bus
        $this->eventBus = $this->getMock(MessageBusSupportingMiddleware::class);

        // Logger
        $this->logger = $this->getMock(LoggerInterface::class);
        $this->projectLogger = $this->getMock(LoggerInterface::class);

        // ProjectEvent
        $this->projectEvent = $this->getMockBuilder(ProjectCreated::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Amqpmessage
        $this->amqpMessage = $this->getMock(AMQPMessage::class);
        $this->amqpMessage->body = 'message';

        // Message failed event
        $this->messageConsumptionFailedEvent = $this->getMockBuilder(MessageConsumptionFailed::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->exception = $this->getMockBuilder(\Exception::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->envelope = $this->getMockBuilder(Envelope::class)
            ->disableOriginalConstructor()
            ->getMock();

        // MessageInEvenloppeSerializer
        $this->messageInEnveloppeSerializer = $this->getMockBuilder(MessageInEnvelopSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->with($this->isInstanceOf(ProjectEvent::class));

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
