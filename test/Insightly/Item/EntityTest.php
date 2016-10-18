<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Entity
     */
    public function testEntity() {
        $entity = new Entity();
        $entity->setId(12345);

        $this->assertEquals(12345, $entity->getId(), 'It correctly returns the entity id.');

        $data = ['id' => 12345];
        $this->assertEquals(json_encode($entity), json_encode($data), 'It correctly json_serializes the entity');
    }
}
