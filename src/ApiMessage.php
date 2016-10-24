<?php

namespace CultuurNet\ProjectAanvraag;

class ApiMessage implements ApiMessageInterface, \JsonSerializable
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $type;

    /**
     * ApiMessage constructor.
     * @param string $message
     * @param string $type
     */
    public function __construct($type = '', $message = '')
    {
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return ApiMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
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
     * @return ApiMessage
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Expose protected properties
     */
    public function jsonSerialize()
    {
        $json = [];

        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        return $json;
    }
}
