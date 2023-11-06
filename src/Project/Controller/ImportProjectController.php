<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportProjectController
{
    use validateRequiredFieldsTraits;

    /**
     * @var MessageBusSupportingMiddleware
     */
    private $commandBus;

    public function __construct(MessageBusSupportingMiddleware $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function importProject(string $uuid, Request $request): JsonResponse
    {
        $postedProject = json_decode($request->getContent());

        $this->validate(
            ['userId', 'name', 'summary', 'groupId', 'testApiKeySapi3', 'liveApiKeySapi3'],
            $postedProject
        );

        $this->commandBus->handle(
            new ImportProject(
                $uuid,
                $postedProject->userId,
                $postedProject->name,
                $postedProject->summary,
                $postedProject->groupId,
                $postedProject->testApiKeySapi3,
                $postedProject->liveApiKeySapi3
            )
        );

        return new JsonResponse();
    }
}
