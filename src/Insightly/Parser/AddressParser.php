<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Address;

/**
 * Address parser
 */
class AddressParser implements ParserInterface
{
    /**
     * Parse an address based on the given data
     *
     * @param mixed $data
     * @return Address
     */
    public static function parseToResult($data)
    {
        $address = new Address();

        $address->setId($data['ADDRESS_ID']);
        $address->setType($data['ADDRESS_TYPE']);
        $address->setStreet($data['STREET']);
        $address->setCity($data['CITY']);
        $address->setPostal($data['POSTCODE']);

        return $address;
    }
}
