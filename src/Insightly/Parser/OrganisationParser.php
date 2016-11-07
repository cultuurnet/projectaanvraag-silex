<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

/**
 * Organisation parser
 */
class OrganisationParser extends PrimaryEntityParser implements ParserInterface
{
    /**
     * Parse an organisation based on the given data
     *
     * @param mixed $data
     * @return Organisation
     */
    public static function parseToResult($data)
    {
        $organisation = new Organisation();
        self::setPrimaryData($organisation, $data);

        $organisation->setId($data['ORGANISATION_ID']);
        $organisation->setName($data['ORGANISATION_NAME']);

        // Parse contact info
        if (!empty($data['CONTACTINFOS'])) {
            $contactList = new EntityList();
            foreach ($data['CONTACTINFOS'] as $item) {
                $contactList->append(ContactInfoParser::parseToResult($item));
            }

            $organisation->setContactInfo($contactList);
        }

        // Parse contact info
        if (!empty($data['ADDRESSES'])) {
            $addressList = new EntityList();
            foreach ($data['ADDRESSES'] as $item) {
                $addressList->append(AddressParser::parseToResult($item));
            }
            $organisation->setAddresses($addressList);
        }

        return $organisation;
    }
}
