<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ\RoutingKeyresolver;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
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

        if ($message instanceof AbstractRetryableMessage) {
            return $message->getAttempts() > 0 ? 'projectaanvraag_delay' : 'asynchronous_commands';
        }

        return 'asynchronous_commands';
    }
}
