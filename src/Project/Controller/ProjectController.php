<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\ApiMessageInterface;
use CultuurNet\ProjectAanvraag\ApiResponse;
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
        $params = $request->request->all();
        $response = new ApiResponse();

        $response->setSuccess();


        // Required fields
        $requiredFields = ['termsAndConditions', 'name', 'summary', 'integrationType'];
        foreach ($requiredFields as $field) {
            if (empty($params[$field]) || !$params[$field]) {
                $response->setError();
            }
        }

        if ($response->isError()) {
            $response->addMessage(ApiMessageInterface::API_MESSAGE_TYPE_ERROR, 'Gelieve alle verplichte velden in te vullen.');
        }

        // Todo: Check coupon code
        // Todo: Create project and return the project id

        return new JsonResponse($response);
    }
}
