<?php

namespace CultuurNet\ProjectAanvraag;

use JMS\Serializer\Annotation\Type;

/**
 * Address object.
 */
class Address
{
    /**
     * @Type("string")
     * @var string
     */
    private $street;

    /**
     * @Type("integer")
     * @var int
     */
    private $postal;

    /**
     * @Type("string")
     * @var string
     */
    private $city;

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
     * @return int
     */
    public function getPostal()
    {
        return $this->postal;
    }

    /**
     * @param int $postal
     * @return Address
     */
    public function setPostal($postal)
    {
        $this->postal = $postal;
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
     * Address constructor.
     * @param string $street
     * @param int $postal
     * @param string $city
     */
    public function __construct($street, $postal, $city)
    {
        $this->street = $street;
        $this->postal = $postal;
        $this->city = $city;
    }
}
