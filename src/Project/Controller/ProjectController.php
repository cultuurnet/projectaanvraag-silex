<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
     * @var AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * ProjectController constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     * @param ProjectServiceInterface $projectService
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(MessageBusSupportingMiddleware $commandBus, ProjectServiceInterface $projectService, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->commandBus = $commandBus;
        $this->projectService = $projectService;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws MissingRequiredFieldsException
     */
    public function createProject(Request $request)
    {
        $postedProject = json_decode($request->getContent());

        $this->validateRequiredFields(
            ['name', 'summary', 'integrationType', 'termsAndConditions'],
            $postedProject
        );

        // Todo: Check coupon code

        /**
         * Dispatch create project command
         */
        $this->commandBus->handle(new CreateProject($postedProject->name, $postedProject->summary, $postedProject->integrationType));

        return new JsonResponse();
    }

    /**
     * Return the list of projects for current person.
     * @param Request $request
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
     * @param int $id
     * @return JsonResponse
     */
    public function getProject($id)
    {
        return new JsonResponse($this->getProjectWithAccessCheck($id, 'view'));
    }

    /**
     * Delete a project.
     * @param int $id
     * @return JsonResponse
     */
    public function deleteProject($id)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');

        /**
         * Dispatch delete project command
         */
        $this->commandBus->handle(new DeleteProject($project));

        return new JsonResponse();
    }

    /**
     * Request an activation for a project.
     */
    public function requestActivation($id, Request $request)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');

        $postedData = json_decode($request->getContent());
        if (!empty($postedData->coupon)) {
            // validate coupon.
            // $this->couponVa..
            $this->commandBus->handle(new ActivateProject($project, $postedData->coupon));
        } else {
            $this->validateRequiredFields(
                ['street', 'number', 'postal', 'city'],
                $postedData
            );

            $this->commandBus->handle(new RequestActivation($project));
        }

        if (empty($project)) {
            throw new NotFoundHttpException('The project was not found');
        }

        if (!$this->authorizationChecker->isGranted('view', $project)) {
            throw new AccessDeniedHttpException();
        }

        return new JsonResponse($project);
    }

    /**
     * Activate a project.
     * @param int $id
     * @return JsonResponse
     */
    public function activateProject($id)
    {
        $project = $this->getProjectWithAccessCheck($id, 'activate');
        $this->commandBus->handle(new ActivateProject($project));

        return new JsonResponse();
    }

    /**
     * Load a project and check if user has access for given operation.
     * @param $id
     * @return Project
     */
    private function getProjectWithAccessCheck($id, $operation)
    {
        $project = $this->projectService->loadProject($id);

        if (empty($project)) {
            throw new NotFoundHttpException('The project was not found');
        }

        if (!$this->authorizationChecker->isGranted($operation, $project)) {
            throw new AccessDeniedHttpException();
        }

        return $project;
    }

    /**
     * Validate if all required fields are in the data.
     * @param \stdClass $data
     * @throws MissingRequiredFieldsException
     */
    private function validateRequiredFields($requiredFields, \stdClass $data = null)
    {
        $emptyFields = [];
        foreach ($requiredFields as $field) {
            if (empty($data->$field)) {
                $emptyFields[] = $field;
            }
        }

        if (!empty($emptyFields)) {
            throw new MissingRequiredFieldsException('Some required fields are missing: ' . implode(', ', $emptyFields));
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws MissingRequiredFieldsException
     */
    public function blockProject($id)
    {
        $project = $this->projectService->loadProject($id);

        if (!$this->authorizationChecker->isGranted('block', $project)) {
            throw new AccessDeniedHttpException();
        }

        /**
         * Dispatch block project command
         */
        $this->commandBus->handle(new BlockProject($project));

        return new JsonResponse();
    }
}
