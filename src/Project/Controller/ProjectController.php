<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
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
        $postedProject = json_decode($request->getContent());

        // Required fields
        $requiredFields = ['name', 'summary', 'integrationType'];
        $emptyFields = [];

        foreach ($requiredFields as $field) {
            if (empty($postedProject->$field)) {
                $emptyFields[] = $field;
            }
        }

        if (!empty($emptyFields) || empty($postedProject->termsAndConditions) || !$postedProject->termsAndConditions) {
            throw new MissingRequiredFieldsException('Some required fields are missing');
        }

        // Todo: Check coupon code
        // Todo: Create project and return the project id

        /**
         * Dispatch create project command
         */
        $this->commandBus->handle(new CreateProject($postedProject->name));

        return new JsonResponse($postedProject);
    }
}
