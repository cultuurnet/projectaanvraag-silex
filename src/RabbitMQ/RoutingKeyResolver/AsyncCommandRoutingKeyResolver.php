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
            return $message->getAttempts() > 5 ? 'projectaanvraag_failed' : 'asynchronous_commands';
        }

        return 'asynchronous_commands';
    }
}
