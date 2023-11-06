<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportProjectControllerTest extends TestCase
{
    /**
     * @var ImportProjectController
     */
    private $controller;

    /**
     * @var MessageBusSupportingMiddleware & MockObject
     */
    private $messageBus;

    /**
     * @var Request & MockObject
     */
    private $request;

    /**
     * @var \stdClass
     */
    private $formData;

    public function setUp()
    {
        $this->messageBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->request = $this->createMock(Request::class);

        $this->controller = new ImportProjectController(
            $this->messageBus
        );

        $this->formData = new \stdClass();
        $this->formData->name = 'name';
        $this->formData->summary = 'summary';
        $this->formData->integrationType = 2;
        $this->formData->termsAndConditions = 'termsAndConditions';
    }

    public function testImportProject()
    {
        $platformUuid = '158cb996-916e-4ee6-8534-e46683555e8c';

        $formData = $this->formData;
        $formData->userId = 'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d';
        $formData->groupId = $formData->integrationType;
        $formData->testApiKeySapi3 = 'a77f461f-3837-49bc-b2a6-1a8f57bf30d6';
        $formData->liveApiKeySapi3 = 'de808573-cfc4-4990-b91b-cf5673b913ac';

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($this->formData));

        $importProject = new ImportProject(
            $platformUuid,
            $this->formData->userId,
            $this->formData->name,
            $this->formData->summary,
            $this->formData->integrationType,
            $this->formData->testApiKeySapi3,
            $this->formData->liveApiKeySapi3
        );
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($importProject);

        $response = $this->controller->importProject($platformUuid, $this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }
}
