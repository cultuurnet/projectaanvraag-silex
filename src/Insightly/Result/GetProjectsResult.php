<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Parser\ProjectParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for the getProjects call.
 */
class GetProjectsResult implements ResponseToResultInterface
{
    /**
     * @return EntityList
     *   Entitylist of parsed projects
     */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);

        $projects = new EntityList();
        if (is_array($body)) {
            foreach ($body as $item) {
                $projects->append(ProjectParser::parseToResult($item));
            }
        }

        return $projects;
    }
}
