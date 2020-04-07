<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Yaml\Yaml;

class IntegrationTypeControllerTest extends TestCase
{
    use JsonAssertionTrait;

    /**
     * @var IntegrationTypeController
     */
    protected $controller;

    /**
     * @var IntegrationTypeStorageInterface|MockObject
     */
    protected $integrationTypeStorageService;


    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $integrationTypesStorageService = $this
            ->getMockBuilder('CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorage')
            ->disableOriginalConstructor()
            ->getMock();

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
