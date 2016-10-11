<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for project related tasks.
 */
class ProjectController
{

    protected $test;

    public function __construct(MessageBusSupportingMiddleware $commandBus)
    {
            $this->test = $commandBus;
    }

    public function listing()
    {

        $command = new CreateProject('test');

        $this->test->handle($command);
        return new JsonResponse('to implement');
    }
}
