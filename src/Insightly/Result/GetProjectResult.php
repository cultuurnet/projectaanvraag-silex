<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Insightly\Parser\ProjectParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for the getProject call.
 */
class GetProjectResult implements ResponseToResultInterface
{
    /**
     * @inheritdoc
     *
     * @return Project
     */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);
        return ProjectParser::parseToResult($body);
    }
}
