<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use JMS\Serializer\Annotation\Type;

class QueueConsumers extends AbstractRetryableMessage implements ConsumerTypeInterface
{
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

    public function getType()
    {
        return $this->type;
    }

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
}
