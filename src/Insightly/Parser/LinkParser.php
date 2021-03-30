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
        $link = new Link();

        $link->setId(!empty($data['LINK_ID']) ? $data['LINK_ID'] : null);
        $link->setContactId(!empty($data['CONTACT_ID']) ? $data['CONTACT_ID'] : null);
        $link->setOpportunityId(!empty($data['OPPORTUNITY_ID']) ? $data['OPPORTUNITY_ID'] : null);
        $link->setOrganisationId(!empty($data['ORGANISATION_ID']) ? $data['ORGANISATION_ID'] : null);
        $link->setProjectId(!empty($data['PROJECT_ID']) ? $data['PROJECT_ID'] : null);
        $link->setSecondProjectId(!empty($data['SECOND_PROJECT_ID']) ? $data['SECOND_PROJECT_ID'] : null);
        $link->setSecondOpportunityId(!empty($data['SECOND_OPPORTUNITY_ID']) ? $data['SECOND_OPPORTUNITY_ID'] : null);
        $link->setRole(!empty($data['ROLE']) ? $data['ROLE'] : null);
        $link->setDetails(!empty($data['DETAILS']) ? $data['DETAILS'] : null);

        // Work around to take into account the new format of links
        // This needs a more robust solution, see https://jira.uitdatabank.be/browse/PROJ-156
        if (isset($data['LINK_OBJECT_NAME'], $data['LINK_OBJECT_ID'])) {
            if ($data['LINK_OBJECT_NAME'] === 'Contact') {
                $link->setContactId($data['LINK_OBJECT_ID']);
            }

            if ($data['LINK_OBJECT_NAME'] === 'Organisation') {
                $link->setContactId($data['LINK_OBJECT_ID']);
            }
        }

        return $link;
    }
}
