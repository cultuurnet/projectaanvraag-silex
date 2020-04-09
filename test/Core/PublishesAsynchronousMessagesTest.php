<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\CallableMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Asynchronous\Publisher\Publisher;

/**
 * Tests PublishesAsynchronousMessages class.
 */
class PublishesAsynchronousMessagesTest extends TestCase
{

    /**
     * @var Publisher|MockObject
     */
    private $publisher;

    /**
     * @var PublishesAsynchronousMessages
     */
    private $publishesAsynchronousMessages;

    protected function setUp(): void
    {
        $this->publisher = $this->createMock(Publisher::class);
        $this->publishesAsynchronousMessages = new PublishesAsynchronousMessages($this->publisher);
    }

    /**
     * @test
     */
    public function testAsynchronousMessageHandling()
    {

        $this->publisher->expects($this->once())->method('publish');

        $next = new CallableMock();
        $message = $this->createMock(AsynchronousMessageInterface::class);

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
        $message = $this->createMock(\stdClass::class);

        $this->publishesAsynchronousMessages->handle($message, $next);

        $this->assertTrue($next->isCalled());
    }
}
