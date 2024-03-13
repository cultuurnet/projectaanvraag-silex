<?php

namespace CultuurNet\ProjectAanvraag\Core\Event;

use CultuurNet\ProjectAanvraag\Core\AbstractRetryableMessage;
use JMS\Serializer\Annotation\Type;

class SyncConsumer extends AbstractRetryableMessage implements ConsumerTypeInterface
{
    /**
     * @Type("string")
     * @var string
     */
    protected $type;

    /**
     * @Type("array<string, string>")
     * @var array
     */
    protected $consumerData;

    /**
     * SyncConsumer constructor.
     * @param string $type
     * @param \CultureFeed_Consumer $consumer
     */
    public function __construct($type, \CultureFeed_Consumer $consumer)
    {
        $this->type = $type;

        // Consumer data to array, because the CultureFeed_Consumer class does not support JMS serializing
        $this->consumerData = $consumer->toPostData();

        // if consumer has one or more admins, add only the first
        if (count($consumer->admins) > 0) {
            $this->consumerData['firstAdmin'] = $consumer->admins[0];
        }

        // implode groups with ,
        $this->consumerData['groups'] = implode(",", $consumer->group);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ?QueueConsumers
    {
        $this->type = $type;
        return null;
    }

    /**
     * @return array
     */
    public function getConsumerData()
    {
        return $this->consumerData;
    }

    /**
     * @param array $consumerData
     * @return SyncConsumer
     */
    public function setConsumerData($consumerData)
    {
        $this->consumerData = $consumerData;
        return $this;
    }
}
