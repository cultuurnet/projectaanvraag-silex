<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

interface ConsumerTypeInterface
{
    const CONSUMER_TYPE_LIVE = 'live';
    const CONSUMER_TYPE_TEST = 'test';

    public function getType(): string;

    public function setType(string $type): ?QueueConsumers;
}
