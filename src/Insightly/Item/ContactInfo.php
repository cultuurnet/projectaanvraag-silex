<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class ContactInfo extends Entity
{
    const CONTACT_INFO_TYPE_EMAIL = 'EMAIL';

    /**
     * @var int
     */
    protected $id;

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
    public function __construct($type = null, $subType = null, $label = null, $detail = null)
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
     */
    public function toInsightly()
    {
        $data = [
            'TYPE' => $this->getType(),
            'SUBTYPE' => $this->getSubType(),
            'LABEL' => $this->getLabel(),
            'DETAIL' => $this->getDetail(),
        ];

        if ($this->getId()) {
            $data['CONTACT_INFO_ID'] = $this->getId();
        }

        return $data;
    }
}
