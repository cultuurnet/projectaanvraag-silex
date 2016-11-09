<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

/**
 * Interface for Insightly entities that need to be unserialized from json.
 */
interface JsonUnserializeInterface
{
    /**
     * Unserialize a json string to an object.
     * @param string $json
     */
    public static function jsonUnSerialize($json);
}
