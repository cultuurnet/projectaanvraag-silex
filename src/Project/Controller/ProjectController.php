<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\ApiMessageInterface;
use CultuurNet\ProjectAanvraag\ApiResponse;
use CultuurNet\ProjectAanvraag\ApiResponseInterface;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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

    public function addProject(Request $request)
    {
        $params = json_decode($request->getContent());

        // Required fields
        $requiredFields = ['name', 'summary', 'integrationType'];
        $emptyFields = [];

        foreach ($requiredFields as $field) {
            if (empty($params[$field])) {
                $emptyFields[] = $field;
            }
        }

        if (!empty($emptyFields)) {
            throw new \InvalidArgumentException('Some required fields are missing');
        }

        // Todo: Check coupon code
        // Todo: Create project and return the project id

        /**
         * Dispatch create project command
         */
        $this->commandBus->handle(new CreateProject($params['name']));

        return new JsonResponse();
    }
}
