<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Parser;

/**
 * Interface to implement for parsers.
 */
interface ParserInterface
{
    /**
     * Parse the data to a result.
     *
     * @param mixed $data
     * @return mixed
     */
    public static function parseToResult($data);
}
