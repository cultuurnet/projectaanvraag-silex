<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Parser\LinkParser;
use Guzzle\Http\Message\Response;

class GetLinksResult implements ResponseToResultInterface
{
    public static function parseToResult(Response $response)
    {
        $links = json_decode($response->getBody(), true);

        return array_map(
            static function ($link) {
                return LinkParser::parseToResult($link);
            },
            $links
        );
    }
}
