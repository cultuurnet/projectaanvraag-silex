<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use CultuurNet\ProjectAanvraag\Voter\ProjectVoter;
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
    public function getProjects()
    {
        return new JsonResponse($this->projectService->loadProjects());
    }

    /**
     * Return a detailled version of a project.
     * @param int $id
     * @return JsonResponse
     */
    public function getProject($id)
    {
        $project = $this->projectService->loadProject($id);

        if (!$this->authorizationChecker->isGranted('view', $project)) {
            throw new AccessDeniedHttpException();
        }

        if (empty($project)) {
            throw new NotFoundHttpException('The project was not found');
        }

        return new JsonResponse($project);
    }

    /**
     * Delete a project.
     * @param int $id
     * @return JsonResponse
     */
    public function deleteProject($id)
    {
        $project = $this->projectService->loadProject($id);

        if (!$this->authorizationChecker->isGranted('edit', $project)) {
            throw new AccessDeniedHttpException();
        }

        /**
         * Dispatch delete project command
         */
        $this->commandBus->handle(new DeleteProject($id));

        return new JsonResponse();
    }
}
