<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class ContactInfoTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test getters and setters + the json serialize.
     */
    public function testAllAndJsonSerialize()
    {
        $contactInfo = new ContactInfo();
        $contactInfo->setId('my-id');
        $this->assertEquals('my-id', $contactInfo->getId());

        $contactInfo->setType('my-type');
        $this->assertEquals('my-type', $contactInfo->getType());

        $contactInfo->setSubType('my-sub-type');
        $this->assertEquals('my-sub-type', $contactInfo->getSubType());

        $contactInfo->setLabel('my-label');
        $this->assertEquals('my-label', $contactInfo->getLabel());

        $contactInfo->setDetail('my-detail');
        $this->assertEquals('my-detail', $contactInfo->getDetail());

        $insightly = $contactInfo->toInsightly();
        $expectedInsightly = [
            'CONTACT_INFO_ID' => 'my-id',
            'TYPE' => 'my-type',
            'SUBTYPE' => 'my-sub-type',
            'LABEL' => 'my-label',
            'DETAIL' => 'my-detail',
        ];
        $this->assertEquals($expectedInsightly, $insightly);

        $this->assertJsonEquals(json_encode($contactInfo), 'Insightly/data/serialized/contact-info.json');
    }
}
