<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Result;

use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Parser\ContactParser;
use Guzzle\Http\Message\Response;

/**
 * Response handler for the getContacts call.
 */
class GetContactsResult implements ResponseToResultInterface
{
  /**
   * @return EntityList
   *   Entitylist of parsed projects
   */
    public static function parseToResult(Response $response)
    {
        $body = json_decode($response->getBody(), true);

        $contacts = new EntityList();
        if (is_array($body)) {
            foreach ($body as $item) {
                $contacts->append(ContactParser::parseToResult($item));
            }
        }

        return $contacts;
    }
}
