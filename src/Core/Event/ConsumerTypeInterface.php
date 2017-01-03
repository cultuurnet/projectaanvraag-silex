<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

interface ConsumerTypeInterface
{
    CONST CONSUMER_TYPE_LIVE = 'live';
    CONST CONSUMER_TYPE_TEST = 'test';

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return QueueConsumers
     */
    public function setType($type);
}
