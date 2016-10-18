<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Parser\PipelineParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for the getPipelines call.
 */
class GetPipelinesResult implements ResponseToResultInterface
{
    /**
     * @inheritdoc
     *
     * @return EntityList
     *   Entitylist of parsed projects
     */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);

        $pipelines = new EntityList();
        if (is_array($body)) {
            foreach ($body as $item) {
                $pipelines->append(PipelineParser::parseToResult($item));
            }
        }

        return $pipelines;
    }
}
