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
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
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
