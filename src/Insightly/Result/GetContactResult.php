<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Insightly\Parser\ContactParser;
use CultuurNet\ProjectAanvraag\Insightly\Parser\ProjectParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for fetching a single contact.
 */
class GetContactResult implements ResponseToResultInterface
{
    /**
     * @inheritdoc
     *
     * @return Project
     */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);
        return ContactParser::parseToResult($body);
    }
}
