<?php

namespace CultuurNet\ProjectAanvraag\Core;

use SimpleBus\Asynchronous\Publisher\Publisher;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

class PublishesAsynchronousMessages implements MessageBusMiddleware
{
    /**
     * @var Publisher
     */
    private $publisher;

    public function __construct(Publisher $publisher)
    {
        $this->publisher = $publisher;
    }

    /**
     * Handle the message by letting the next middleware handle it. If no handler is defined for this message, then
     * it is published to be processed asynchronously
     *
     * @param object $message
     * @param callable $next
     */
    public function handle($message, callable $next)
    {
        if ($message instanceof AsynchronousMessageInterface) {
            $this->publisher->publish($message);
        } else {
            $next($message);
        }
    }
}
