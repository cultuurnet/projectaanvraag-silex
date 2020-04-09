<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EntityListTest extends TestCase
{
    public function testEntityListExceptionHandling()
    {
        $data = [new \stdClass()];
        $this->expectException(InvalidArgumentException::class);
        new EntityList($data);
    }
}
