<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Parser\OrganisationParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for the getOrganisation call.
 */
class GetOrganisationResult implements ResponseToResultInterface
{
    /**
     * @return Organisation
     */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);
        return OrganisationParser::parseToResult($body);
    }
}
