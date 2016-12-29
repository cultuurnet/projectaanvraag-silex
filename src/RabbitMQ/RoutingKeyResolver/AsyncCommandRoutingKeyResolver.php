<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\RoutingKeyresolver;

use SimpleBus\Asynchronous\Routing\RoutingKeyResolver;

class AsyncCommandRoutingKeyResolver implements RoutingKeyResolver
{
    /**
     * Always return the asynchronous_commands routing key.
     *
     * {@inheritdoc}
     */
    public function resolveRoutingKeyFor($message)
    {
        return 'asynchronous_commands';
    }
}
