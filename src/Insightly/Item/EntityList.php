<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

/**
 * A list of Insightly entities.
 */
class EntityList extends \ArrayIterator
{
  /**
   * EntityList constructor.
   * @param array $array
   * @param int $flags
   */
  public function __construct(array $array = [], $flags = 0)
  {
    if (count($array) > 0) {
      $first_item = current($array);
      if (!$first_item instanceof EntityInterface) {
        throw new \InvalidArgumentException('The list should contain only objects of type EntityInterface');
      }
    }

    parent::__construct($array, $flags);
  }
}