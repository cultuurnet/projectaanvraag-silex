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

        return $organisation;
    }
}
