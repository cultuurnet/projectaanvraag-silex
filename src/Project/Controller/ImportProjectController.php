<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Psr7\Response;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\Request;

class ImportProjectController
{
    use ValidateRequiredFieldsTrait;

    /**
     * @var MessageBusSupportingMiddleware
     */
    private $commandBus;

    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    public function __construct(MessageBusSupportingMiddleware $commandBus, EntityRepository $projectRepository)
    {
        $this->commandBus = $commandBus;
        $this->projectRepository = $projectRepository;
    }

    public function importProject(string $uuid, Request $request): Response
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

        return new Response();
    }
}
