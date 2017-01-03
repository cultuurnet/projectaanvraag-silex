<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

use CultuurNet\ProjectAanvraag\Core\AsynchronousMessageInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedInterface;
use CultuurNet\ProjectAanvraag\Core\MessageAttemptedTrait;
use CultuurNet\ProjectAanvraag\RabbitMQ\DelayableMessageInterface;
use JMS\Serializer\Annotation\Type;

class QueueConsumers implements AsynchronousMessageInterface, DelayableMessageInterface, MessageAttemptedInterface
{
    use MessageAttemptedTrait;

    CONST CONSUMER_TYPE_LIVE = 'live';
    CONST CONSUMER_TYPE_TEST = 'test';

    /**
     * @Type("string")
     * @var string
     */
    protected $type;

    /**
     * @Type("integer")
     * @var int
     */
    protected $start;

    /**
     * @Type("integer")
     * @var int
     */
    protected $max;

    /**
     * @var int
     */
    protected $delay = 0;

    /**
     * QueueConsumersEvent constructor.
     * @param string $type
     * @param int $start
     * @param int $max
     */
    public function __construct($type, $start = 0, $max = 100)
    {
        $this->type = $type;
        $this->start = $start;
        $this->max = $max;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return QueueConsumers
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param int $start
     * @return QueueConsumers
     */
    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param int $max
     * @return QueueConsumers
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Set the delay in milliseconds
     * @param int $delay
     * @return QueueConsumers
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
}
