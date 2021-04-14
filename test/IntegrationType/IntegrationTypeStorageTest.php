<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class IntegrationTypeStorageTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * Test IntegrationType
     */
    public function testIntegrationTypeStorage()
    {
        $integrationTypeStorage = new IntegrationTypeStorage(__DIR__ . '/data/config/integration_types.yml');
        $types = $integrationTypeStorage->getIntegrationTypes();

        $this->assertJsonEquals(json_encode($types), 'IntegrationType/data/serialized/integration_types.json');

        // Test single load
        $type = $integrationTypeStorage->load('api');
        $this->assertJsonEquals(json_encode($type), 'IntegrationType/data/serialized/integration_type.json');
    }
}
