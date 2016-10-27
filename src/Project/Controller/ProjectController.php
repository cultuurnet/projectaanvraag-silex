<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for project related tasks.
 */
class ProjectController
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $commandBus;

    /**
     * @var ProjectServiceInterface
     */
    protected $projectService;

    /**
     * ProjectController constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     * @param ProjectServiceInterface $projectService
     */
    public function __construct(MessageBusSupportingMiddleware $commandBus, ProjectServiceInterface $projectService)
    {
        $this->commandBus = $commandBus;
        $this->projectService = $projectService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws MissingRequiredFieldsException
     */
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

        /**
         * Dispatch create project command
         */
        $this->commandBus->handle(new CreateProject($postedProject->name, $postedProject->summary, $postedProject->integrationType));

        return new JsonResponse();
    }

    /**
     * Return the list of projects for current person.
     * @return JsonResponse
     */
    public function getProjects(Request $request)
    {

        $name = $request->query->get('name', '');
        $start = $request->query->get('start', 0);
        $max = $request->query->get('max', 0);

        return new JsonResponse($this->projectService->searchProjects($start, $max, $name));
    }

    /**
     * Return a detailled version of a project.
     * @return JsonResponse
     */
    public function getProject($id)
    {
        $project = $this->projectService->loadProject($id);

        if (empty($project)) {
            throw new NotFoundHttpException('The project was not found');
        }
        return new JsonResponse($project);
    }

    /**
     * Delete a project.
     * @return JsonResponse
     */
    public function deleteProject($id)
    {
        /**
         * Dispatch delete project command
         */
        $this->commandBus->handle(new DeleteProject($id));

        return new JsonResponse();
    }
}
