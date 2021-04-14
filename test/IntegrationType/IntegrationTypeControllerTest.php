<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IntegrationTypeControllerTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * @var IntegrationTypeController
     */
    protected $controller;

    /**
     * @var IntegrationTypeStorageInterface & MockObject
     */
    protected $integrationTypeStorageService;

    public function setUp()
    {
        $integrationTypesStorageService = $this->createMock(IntegrationTypeStorage::class);

        $this->integrationTypeStorageService = $integrationTypesStorageService;
        $this->controller = new IntegrationTypeController($this->integrationTypeStorageService);
    }

    /**
     * Test IntegrationType
     */
    public function testIntegrationTypeController()
    {
        $json = file_get_contents(__DIR__ . '/data/serialized/integration_types.json');

        $this->integrationTypeStorageService
            ->expects($this->any())
            ->method('getIntegrationTypes')
            ->willReturn(json_decode($json));

        $response = $this->controller->listing();
        $this->assertJsonStringEqualsJsonFile(__DIR__ . '/data/serialized/integration_types.json', $response->getContent());
    }
}
