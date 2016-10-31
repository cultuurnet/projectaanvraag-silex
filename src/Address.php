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
     * @Type("string")
     * @var string
     */
    private $number;

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
     * Address constructor.
     * @param string $street
     * @param string $number
     * @param int $postal
     * @param string $city
     */
    public function __construct($street, $number, $postal, $city)
    {
        $this->street = $street;
        $this->number = $number;
        $this->postal = $postal;
        $this->city = $city;
    }
}
