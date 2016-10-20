<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

use CultuurNet\ProjectAanvraag\Insightly\Item\Tag;

/**
 * Tag parser
 */
class TagParser implements ParserInterface
{
    /**
     * Parse a tag based on the given data
     *
     * @param mixed $data
     * @return Tag The parsed project.
     */
    public static function parseToResult($data)
    {
        $tag = new Tag();
        $tag->setId(!empty($data['TAG_NAME']) ? $data['TAG_NAME'] : null);

        return $tag;
    }
}
