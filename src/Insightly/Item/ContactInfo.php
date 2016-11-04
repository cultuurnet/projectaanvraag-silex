<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\InsightlySerializable;

class ContactInfo extends Entity implements \JsonSerializable, InsightlySerializable
{

    const TYPE_EMAIL = 'email';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $detail;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ContactInfo
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return ContactInfo
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     * @return ContactInfo
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $json = [];
        foreach ($this as $key => $value) {
            $json[$key] = $value;
        }

        return $json;
    }

    /**
     * {@inheritdoc}
     */
    public function toInsightly()
    {
        $data = [
            'CONTACT_INFO_ID' => $this->getId(),
            'TYPE' => $this->getType(),
            'LABEL' => $this->getLabel(),
            'DETAIL' => $this->getDetail(),
        ];

        return array_filter($data);
    }
}