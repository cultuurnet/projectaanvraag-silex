<?php

namespace CultuurNet\ProjectAanvraag\RabbitMQ;

/**
 * Class DelayableMessageInterface for marking a message as delayable
 */
interface DelayableMessageInterface
{
    /**
     * @return int
     * Returns the delay in milliseconds
     */
    public function getDelay();
}
