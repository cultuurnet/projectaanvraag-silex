<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\AbstractInsightlyClientTest;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssertionTrait;

    /**
     * Test getters and setters + the json serialize.
     */
    public function testAllAndJsonSerialize()
    {
        $pipeline = new Pipeline();
        $pipeline->setId('my-id');
        $this->assertEquals('my-id', $pipeline->getId());

        $pipeline->setName('my-name');
        $this->assertEquals('my-name', $pipeline->getName());

        $pipeline->setForOpportunities(true);
        $this->assertEquals(true, $pipeline->isForOpportunities());

        $pipeline->setForProjects(true);
        $this->assertEquals(true, $pipeline->isForProjects());

        $pipeline->setOwnerUserId(123);
        $this->assertEquals(123, $pipeline->getOwnerUserId());

        $this->assertJsonEquals(json_encode($pipeline), 'Insightly/data/serialized/pipeline.json');
    }
}
