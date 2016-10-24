<?php

namespace CultuurNet\ProjectAanvraag;

class ApiResponse implements ApiResponseInterface, \JsonSerializable
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array
     */
    protected $errors;

    /**
     * @var ApiMessage[]
     */
    protected $messages;

    /**
     * @var string
     */
    protected $type;

    /**
     * ApiResponse constructor.
     * @param $type
     * @param array $data
     * @param array $messages
     */
    public function __construct($type = '', $data = [], $messages = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->messages = $messages;
    }

    /**
     * Add an ApiMessage to the stack
     * @param $type
     * @param $message
     */
    public function addMessage($type, $message)
    {
        $this->messages[$type][] = new ApiMessage($type, $message);
    }

    /**
     * @return ApiMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ApiMessage[] $messages
     * @return ApiResponse
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ApiResponse
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return ApiResponse
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
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
     * @return ApiResponse
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set the response type as error
     */
    public function setError()
    {
        $this->type = ApiResponseInterface::API_REPONSE_TYPE_ERROR;
    }

    /**
     * Set the response type as success
     */
    public function setSuccess()
    {
        $this->type = ApiResponseInterface::API_RESPONSE_TYPE_SUCCESS;
    }

    /**
     * Check if the response type is an error
     */
    public function isError()
    {
        return $this->type === ApiResponseInterface::API_REPONSE_TYPE_ERROR;
    }

    /**
     * Check if the response type is success
     */
    public function isSuccess()
    {
        return $this->type === ApiResponseInterface::API_RESPONSE_TYPE_SUCCESS;
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
