<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for project related tasks.
 */
class ProjectController
{
    protected $commandBus;

    public function __construct(MessageBusSupportingMiddleware $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function listing()
    {
        return new JsonResponse(['key' => 'value']);
    }
}
