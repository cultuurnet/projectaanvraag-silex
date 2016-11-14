<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\InsightlySerializableInterface;

class Address implements \JsonSerializable, InsightlySerializableInterface, JsonUnserializeInterface
{

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
    protected $street;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $postal;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Address
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
     * @return Address
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return Address
     */
    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * @param string $postal
     * @return Address
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;
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
     * @inheritDoc
     */
    public static function jsonUnSerialize($json)
    {
        $address = new self();

        $address->setId(!empty($json->id) ? $json->id : null);
        $address->setCity(!empty($json->city) ? $json->city : null);
        $address->setPostal(!empty($json->postal) ? $json->postal : null);
        $address->setStreet(!empty($json->street) ? $json->street : null);
        $address->setType(!empty($json->type) ? $json->type : null);

        return $address;
    }

    /**
     * {@inheritdoc}
     */
    public function toInsightly()
    {
        $data = [
            'ADDRESS_ID' => $this->getId(),
            'ADDRESS_TYPE' => $this->getType(),
            'STREET' => $this->getStreet(),
            'CITY' => $this->getCity(),
            'POSTCODE' => $this->getPostal(),
        ];

        return array_filter($data);
    }
}
