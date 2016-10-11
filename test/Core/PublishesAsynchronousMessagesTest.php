<?php

namespace CultuurNet\ProjectAanvraag\Core;

use Akamon\MockeryCallableMock\MockeryCallableMock;
use CultuurNet\ProjectAanvraag\CallableMock;
use SimpleBus\Asynchronous\Publisher\Publisher;

/**
 * Tests PublishesAsynchronousMessages class.
 */
class PublishesAsynchronousMessagesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Publisher|PHPUnit_Framework_MockObject_MockObject
     */
    private $publisher;

    /**
     * @var PublishesAsynchronousMessages
     */
    private $publishesAsynchronousMessages;

    public function setUp()
    {
        $this->publisher = $this->getMock(Publisher::class);
        $this->publishesAsynchronousMessages = new PublishesAsynchronousMessages($this->publisher);
    }

    /**
     * @test
     */
    public function testAsynchronousMessageHandling()
    {

        $this->publisher->expects($this->once())->method('publish');

        $next = new CallableMock();
        $message = $this->getMock(AsynchronousMessageInterface::class);

        $this->publishesAsynchronousMessages->handle($message, $next);

        $this->assertFalse($next->isCalled());
    }

    /**
     * @test
     */
    public function testSynchronousMessageHandling()
    {

        $this->publisher->expects($this->never())->method('publish');

        $next = new CallableMock();
        $message = $this->getMock(\stdClass::class);

        $this->publishesAsynchronousMessages->handle($message, $next);

        $this->assertTrue($next->isCalled());
    }
}
