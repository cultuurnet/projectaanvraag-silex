<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;

/**
 * Contact parser
 */
class ContactParser implements ParserInterface
{
    /**
     * Parse a contact based on the given data
     *
     * @param mixed $data
     * @return Contact The parsed contact.
     */
    public static function parseToResult($data)
    {
        $contact = new Contact();
        $contact->setId(!empty($data['CONTACT_ID']) ? $data['CONTACT_ID'] : null);
        $contact->setFirstName(!empty($data['FIRST_NAME']) ? $data['FIRST_NAME'] : null);
        $contact->setLastName(!empty($data['LAST_NAME']) ? $data['LAST_NAME'] : null);

        // Todo (when required): Parse contact infos

        return $contact;
    }
}
