<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\RabbitMQ\DelayableMessageInterface;
use JMS\Serializer\Annotation\Type;

abstract class AbstractRetryableMessage implements AsynchronousMessageInterface, DelayableMessageInterface, MessageAttemptedInterface
{
    /**
     * @var int
     * @Type("string")
     * Number of times the message was attempted to process.
     */
    protected $attempts;

    /**
     * @Type("integer")
     * @var int
     */
    protected $delay = 0;

    /**
     * Set the delay in milliseconds
     * @param int $delay
     * @return AbstractRetryableMessage
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Add an attempt to the message.
     */
    public function attempt()
    {
        $this->attempts += 1;
    }

    /**
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     * @return AbstractRetryableMessage
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }
}
