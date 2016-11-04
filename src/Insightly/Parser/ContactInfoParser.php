<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;

/**
 * Contact info parser
 */
class ContactInfoParser implements ParserInterface
{
    /**
     * Parse contact info based on the given data
     *
     * @param mixed $data
     * @return ContactInfo
     */
    public static function parseToResult($data)
    {
        $info = new ContactInfo();

        $info->setId($data['CONTACT_INFO_ID']);
        $info->setType($data['TYPE']);
        $info->setLabel($data['LABEL']);
        $info->setDetail($data['DETAIL']);

        return $info;
    }
}
