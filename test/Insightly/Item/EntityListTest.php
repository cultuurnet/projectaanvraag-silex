<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class EntityListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test EntityList
     * @expectedException \InvalidArgumentException
     */
    public function testEntityListExceptionHandling() {
        $data = [new \stdClass()];
        new EntityList($data);
    }
}
