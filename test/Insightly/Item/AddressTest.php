<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test getters and setters + the json serialize.
     */
    public function testAllAndJsonSerialize()
    {
        $address = new Address();
        $address->setId('my-id');
        $this->assertEquals('my-id', $address->getId());

        $address->setType('my-type');
        $this->assertEquals('my-type', $address->getType());

        $address->setStreet('my-street');
        $this->assertEquals('my-street', $address->getStreet());

        $address->setCity('my-city');
        $this->assertEquals('my-city', $address->getCity());

        $address->setPostal('my-postal');
        $this->assertEquals('my-postal', $address->getPostal());

        $insightly = $address->toInsightly();
        $expectedInsightly = [
            'ADDRESS_BILLING_STREET' => 'my-street',
            'ADDRESS_BILLING_CITY' => 'my-city',
            'ADDRESS_BILLING_POSTCODE' => 'my-postal',
        ];
        $this->assertEquals($expectedInsightly, $insightly);

        $this->assertJsonEquals(json_encode($address), 'Insightly/data/serialized/address.json');
    }
}
