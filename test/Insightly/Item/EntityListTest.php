<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use PHPUnit\Framework\TestCase;

class EntityListTest extends TestCase
{
    /**
     * Test EntityList
     * @expectedException \InvalidArgumentException
     */
    public function testEntityListExceptionHandling()
    {
        $data = [new \stdClass()];
        new EntityList($data);
    }
}
