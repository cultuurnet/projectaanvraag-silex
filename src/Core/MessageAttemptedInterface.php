<?php

namespace CultuurNet\ProjectAanvraag\Core;

interface MessageAttemptedInterface
{
    /**
     * Add an attempt to the message.
     */
    public function attempt();

    /**
     * @return int
     */
    public function getAttempts();

    /**
     * @param int $attempts
     * @return MessageAttemptedTrait
     */
    public function setAttempts($attempts);
}
