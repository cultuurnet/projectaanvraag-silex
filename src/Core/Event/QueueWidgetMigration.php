<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use JMS\Serializer\Annotation\Type;

class QueueWidgetMigration extends AbstractRetryableMessage
{

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
     * QueueWidgetMigrationEvent constructor.
     * @param int $start
     * @param int $max
     */
    public function __construct($start = 0, $max = 100)
    {
        $this->start = $start;
        $this->max = $max;
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
     * @return QueueWidgetMigration
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
     * @return QueueWidgetMigration
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }
}
