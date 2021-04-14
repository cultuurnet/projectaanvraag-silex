<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test getters and setters + the json serialize.
     */
    public function testAllAndJsonSerialize()
    {
        $contact = new Contact();
        $contact->setId('my-id');
        $this->assertEquals('my-id', $contact->getId());

        $contact->setFirstName('my-first-name');
        $this->assertEquals('my-first-name', $contact->getFirstName());

        $contact->setLastName('my-last-name');
        $this->assertEquals('my-last-name', $contact->getLastName());

        $contactInfo = [];
        $contactInfo[0] = new ContactInfo();
        $contactInfo[0]->setType('type-1');
        $contactInfo[0]->setSubType('sub-type-1');
        $contactInfo[0]->setLabel('label-1');
        $contactInfo[0]->setDetail('detail-1');
        $contact->addContactInfo('type-1', 'detail-1', 'sub-type-1', 'label-1');

        $contactInfo[1] = new ContactInfo();
        $contactInfo[1]->setType(ContactInfo::TYPE_EMAIL);
        $contactInfo[1]->setSubType('sub-type-2');
        $contactInfo[1]->setLabel('label-2');
        $contactInfo[1]->setDetail('detail-2');
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'detail-2', 'sub-type-2', 'label-2');
        $this->assertEquals($contactInfo, $contact->getContactInfos());

        $contactInfo[2] = new ContactInfo();
        $contactInfo[2]->setType('type-3');
        $contactInfo[2]->setSubType('sub-type-3');
        $contactInfo[2]->setLabel('label-3');
        $contactInfo[2]->setDetail('detail-3');
        $contact->setContactInfos($contactInfo);
        $this->assertEquals($contactInfo, $contact->getContactInfos());

        $insightly = $contact->toInsightly();
        $expectedInsightly = [
            'CONTACT_ID' => 'my-id',
            'FIRST_NAME' => 'my-first-name',
            'LAST_NAME' => 'my-last-name',
            'EMAIL_ADDRESS' => 'detail-2',
        ];
        $this->assertEquals($expectedInsightly, $insightly);
    }
}
