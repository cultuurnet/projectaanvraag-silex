<?php

namespace CultuurNet\ProjectAanvraag;

class AddressTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test getters and setters.
     */
    public function testGettersAndSetters()
    {
        // Test construct + getters.
        $address = new Address('street', 9000, 'Gent');
        $this->assertEquals('street', $address->getStreet());
        $this->assertEquals(9000, $address->getPostal());
        $this->assertEquals('Gent', $address->getCity());

        // Test setters.
        $address->setStreet('street2');
        $address->setPostal(9001);
        $address->setCity('Gent2');
        $this->assertEquals('street2', $address->getStreet());
        $this->assertEquals(9001, $address->getPostal());
        $this->assertEquals('Gent2', $address->getCity());
    }
}
