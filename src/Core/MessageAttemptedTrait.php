<?php

namespace CultuurNet\ProjectAanvraag\Core;

use JMS\Serializer\Annotation\Type;

trait MessageAttemptedTrait
{
    /**
     * @var int
     * @Type("string")
     * Number of times the message was attempted to process.
     */
    protected $attempts;

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
     * @return MessageAttemptedTrait
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }
}
