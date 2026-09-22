<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportProjectControllerTest extends TestCase
{
    private const PLATFORM_UUID = '158cb996-916e-4ee6-8534-e46683555e8c';

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

    public function setUp()
    {
        $this->messageBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->request = $this->createMock(Request::class);

        $this->controller = new ImportProjectController($this->messageBus);
    }

    public function testImportProject()
    {
        $formData = $this->formData();
        $formData->testApiKeySapi3 = 'a77f461f-3837-49bc-b2a6-1a8f57bf30d6';
        $formData->liveApiKeySapi3 = 'de808573-cfc4-4990-b91b-cf5673b913ac';

        $this->givenTheRequestContains($formData);

        $importProject = new ImportProject(
            self::PLATFORM_UUID,
            $formData->userId,
            $formData->name,
            $formData->summary,
            $formData->groupId,
            $formData->testApiKeySapi3,
            $formData->liveApiKeySapi3,
            $formData->testClientId,
            $formData->liveClientId,
            $formData->state
        );
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($importProject);

        $response = $this->controller->importProject(self::PLATFORM_UUID, $this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }

    public function testImportProjectWithoutApiKeys()
    {
        $formData = $this->formData();
        $formData->testApiKeySapi3 = null;
        $formData->liveApiKeySapi3 = null;

        $this->givenTheRequestContains($formData);

        $importProject = new ImportProject(
            self::PLATFORM_UUID,
            $formData->userId,
            $formData->name,
            $formData->summary,
            $formData->groupId,
            null,
            null,
            $formData->testClientId,
            $formData->liveClientId,
            $formData->state
        );
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($importProject);

        $response = $this->controller->importProject(self::PLATFORM_UUID, $this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It accepts a payload without api keys');
    }

    public function testImportProjectWithoutApiKeyProperties()
    {
        $formData = $this->formData();

        $this->givenTheRequestContains($formData);

        $importProject = new ImportProject(
            self::PLATFORM_UUID,
            $formData->userId,
            $formData->name,
            $formData->summary,
            $formData->groupId,
            null,
            null,
            $formData->testClientId,
            $formData->liveClientId,
            $formData->state
        );
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($importProject);

        $response = $this->controller->importProject(self::PLATFORM_UUID, $this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It accepts a payload without api keys at all');
    }

    /**
     * @dataProvider requiredFieldProvider
     */
    public function testImportProjectRequiresField(string $field)
    {
        $formData = $this->formData();
        unset($formData->$field);

        $this->givenTheRequestContains($formData);

        $this->messageBus
            ->expects($this->never())
            ->method('handle');

        $this->expectException(MissingRequiredFieldsException::class);
        $this->expectExceptionMessage('Some required fields are missing: ' . $field);

        $this->controller->importProject(self::PLATFORM_UUID, $this->request);
    }

    public function requiredFieldProvider(): array
    {
        return [
            'userId' => ['userId'],
            'name' => ['name'],
            'summary' => ['summary'],
            'groupId' => ['groupId'],
            'testClientId' => ['testClientId'],
            'liveClientId' => ['liveClientId'],
            'state' => ['state'],
        ];
    }

    private function formData(): \stdClass
    {
        $formData = new \stdClass();
        $formData->name = 'name';
        $formData->summary = 'summary';
        $formData->userId = 'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d';
        $formData->groupId = 2;
        $formData->testClientId = '550e8400-e29b-41d4-a716-446655440000';
        $formData->liveClientId = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
        $formData->state = 'active';

        return $formData;
    }

    private function givenTheRequestContains(\stdClass $formData): void
    {
        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($formData));
    }
}
