<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Link;

/**
 * Link parser
 */
class LinkParser implements ParserInterface
{
    /**
     * Parse a link based on the given data
     *
     * @param mixed $data
     * @return Link The parsed project.
     */
    public static function parseToResult($data)
    {
        $link_type = null;
        $link_types = [
            'CONTACT_ID',
            'OPPORTUNITY_ID',
            'ORGANISATION_ID',
            'PROJECT_ID',
            'SECOND_PROJECT_ID',
            'SECOND_OPPORTUNITY_ID',
        ];

        foreach ($link_types as $type) {
            if (!empty($data[$type])) {
                $link_type = $type;
            }
        }

        $link = new Link($link_type, $data[$link_type]);

        $link->setId(!empty($data['LINK_ID']) ? $data['LINK_ID'] : null);
        $link->setRole(!empty($data['ROLE']) ? $data['ROLE'] : null);
        $link->setDetails(!empty($data['DETAILS']) ? $data['DETAILS'] : null);

        return $link;
    }
}
