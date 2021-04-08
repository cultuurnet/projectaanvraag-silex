<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test getters and setters + the json serialize.
     */
    public function testAllAndJsonSerialize()
    {
        $tag = new Tag();
        $tag->setId('my-id');
        $this->assertEquals('my-id', $tag->getName());

        $this->assertJsonEquals(json_encode($tag), 'Insightly/data/serialized/tag.json');

        $insightly = $tag->toInsightly();
        $expectedInsightly = [
            'TAG_NAME' => 'my-id',
        ];
        $this->assertEquals($expectedInsightly, $insightly);
    }
}
