<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ImportProjectController
{
    use ValidateRequiredFieldsTrait;

    /**
     * The platform only syncs widget integrations, so the integration type is always "Widgets".
     * See the 24378 entry in integration_types.yml.
     */
    private const WIDGETS_INTEGRATION_TYPE_ID = 24378;

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
            ['userId', 'name', 'summary', 'testClientId', 'liveClientId', 'state'],
            $postedProject
        );

        $this->commandBus->handle(
            new ImportProject(
                $uuid,
                $postedProject->userId,
                $postedProject->name,
                $postedProject->summary,
                (int) ($postedProject->groupId ?? self::WIDGETS_INTEGRATION_TYPE_ID),
                $postedProject->testApiKeySapi3 ?? null,
                $postedProject->liveApiKeySapi3 ?? null,
                $postedProject->testClientId,
                $postedProject->liveClientId,
                $postedProject->state
            )
        );

        return new JsonResponse();
    }
}
