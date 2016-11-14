<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\InsightlySerializableInterface;

class ContactInfo extends Entity implements \JsonSerializable, InsightlySerializableInterface, JsonUnserializeInterface
{
    const TYPE_EMAIL = 'email';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $subType;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $detail;

    /**
     * ContactInfo constructor.
     * @param string $type
     * @param string $subType
     * @param string $label
     * @param string $detail
     */
    public function __construct($type = null, $detail = null, $subType = null, $label = null)
    {
        $this->type = $type;
        $this->subType = $subType;
        $this->label = $label;
        $this->detail = $detail;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ContactInfo
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getSubType()
    {
        return $this->subType;
    }

    /**
     * @param string $subType
     * @return ContactInfo
     */
    public function setSubType($subType)
    {
        $this->subType = $subType;
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
     * Serializes a Contact info to an Insightly accepted array
     * @return array
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
     * @inheritDoc
     */
    public static function jsonUnSerialize($json)
    {
        $contactInfo = new self();

        $contactInfo->setId(!empty($json->id) ? $json->id : null);
        $contactInfo->setDetail(!empty($json->detail) ? $json->detail : null);
        $contactInfo->setLabel(!empty($json->label) ? $json->label : null);
        $contactInfo->setType(!empty($json->type) ? $json->type : null);

        return $contactInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function toInsightly()
    {
        $data = [
            'CONTACT_INFO_ID' => $this->getId(),
            'TYPE' => $this->getType(),
            'SUBTYPE' => $this->getSubType(),
            'LABEL' => $this->getLabel(),
            'DETAIL' => $this->getDetail(),
        ];

        return array_filter($data);
    }
}
