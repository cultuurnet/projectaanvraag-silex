<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\AbstractInsightlyClientTest;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;

class AddressTest extends \PHPUnit_Framework_TestCase
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
            'ADDRESS_ID' => 'my-id',
            'ADDRESS_TYPE' => 'my-type',
            'STREET' => 'my-street',
            'CITY' => 'my-city',
            'POSTCODE' => 'my-postal',
        ];
        $this->assertEquals($expectedInsightly, $insightly);

        $this->assertJsonEquals(json_encode($address), 'Insightly/data/serialized/address.json');
    }
}
