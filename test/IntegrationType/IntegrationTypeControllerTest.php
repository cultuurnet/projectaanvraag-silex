<?php

namespace CultuurNet\ProjectAanvraag\IntegrationType;

use CultuurNet\ProjectAanvraag\IntegrationType\Controller\IntegrationTypeController;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use Symfony\Component\HttpFoundation\JsonResponse;

class IntegrationTypeControllerTest extends \PHPUnit_Framework_TestCase
{
    use JsonAssertionTrait;

    /**
     * Test IntegrationType
     */
    public function testIntegrationTypeController()
    {
        $integrationTypeStorage = new IntegrationTypeStorage(__DIR__ . '/data/config/integration_types.yml');
        $integrationTypeController = new IntegrationTypeController($integrationTypeStorage);

        $actual = $integrationTypeController->listing();
        $expected =  new JsonResponse($integrationTypeStorage->getIntegrationTypes());

        $this->assertEquals($actual, $expected, 'It correctly returns the json response');
    }
}
