<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Coupon\CouponValidatorInterface;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
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
     * @var InsightlyClientInterface
     */
    protected $insightlyclient;

    /**
     * @var CouponValidatorInterface
     */
    protected $couponValidator;

    /**
     * ProjectController constructor.
     * @param MessageBusSupportingMiddleware $commandBus
     * @param ProjectServiceInterface $projectService
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param CouponValidatorInterface $couponValidator
     * @param InsightlyClientInterface $insightlyClient
     */
    public function __construct(MessageBusSupportingMiddleware $commandBus, ProjectServiceInterface $projectService, AuthorizationCheckerInterface $authorizationChecker, CouponValidatorInterface $couponValidator, InsightlyClientInterface $insightlyClient)
    {
        $this->commandBus = $commandBus;
        $this->projectService = $projectService;
        $this->authorizationChecker = $authorizationChecker;
        $this->insightlyclient = $insightlyClient;
        $this->couponValidator = $couponValidator;
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

        $coupon = null;
        if (!empty($postedProject->coupon)) {
            $this->couponValidator->validateCoupon($postedProject->coupon);
            $coupon = $postedProject->coupon;
        }

        /**
         * Dispatch create project command
         */
        $this->commandBus->handle(new CreateProject($postedProject->name, $postedProject->summary, $postedProject->integrationType, $coupon));

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
     * @param int $id
     * @return JsonResponse
     * @throws MissingRequiredFieldsException
     */
    public function blockProject($id)
    {
        $project = $this->getProjectWithAccessCheck($id, 'block');

        /**
         * Dispatch block project command
         */
        $this->commandBus->handle(new BlockProject($project));

        return new JsonResponse($project);
    }

    /**
     * Request an activation for a project.
     */
    public function requestActivation($id, Request $request)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');

        $postedData = json_decode($request->getContent());
        if (!empty($postedData->coupon)) {
            $this->couponValidator->validateCoupon($postedData->coupon);
            $this->commandBus->handle(new ActivateProject($project, $postedData->coupon));
        } else {
            $this->validateRequiredFields(
                ['name', 'street', 'postal', 'city'],
                $postedData
            );

            $vat = !empty($postedData->vat) ? $postedData->vat : '';

            $payment = !empty($postedData->email) ? $postedData->email : '';

            $address = new Address($postedData->street, $postedData->postal, $postedData->city);
            $this->commandBus->handle(new RequestActivation($project, $postedData->name, $address, $vat, $payment));
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

        return new JsonResponse($project);
    }

    /**
     * Update the content filter for a given project.
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function updateContentFilter(Request $request, $id)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');
        $data = json_decode($request->getContent());

        $this->validateRequiredFields(['contentFilter'], $data);

        $this->projectService->updateContentFilter($project, $data->contentFilter);

        return new JsonResponse($project);
    }

    /**
     * Gets the linked organisation for the project.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getOrganisation($id)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');
        $organisation = $this->getOrganisationByProject($project);

        if (!$organisation) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($organisation);
    }

    /**
     * Update an organisation.
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrganisation($id, Request $request)
    {
        $project = $this->getProjectWithAccessCheck($id, 'edit');

        $postedData = json_decode($request->getContent());
        $this->validateRequiredFields(
            ['name', 'addresses'],
            $postedData
        );

        // If the postedData contains id's, compare them to the currently linked organisation
        // This is to ensure that the user is editing his own organisation, address and contact info
        $currentOrganisation = $this->getOrganisationByProject($project);
        $jsonOrganisation = json_decode(json_encode($currentOrganisation));

        $postedIds = $this->getIdsFromData($postedData);
        $currentIds = $this->getIdsFromData($jsonOrganisation);
        if (!empty(array_diff($currentIds, $postedIds))) {
            throw new AccessDeniedHttpException('Not allowed to edit this information');
        }

        $postedOrganisation = Organisation::jsonUnSerialize($request->getContent());

        // Keep the original links
        $postedOrganisation->setLinks($currentOrganisation->getLinks());

        // Update the organisation
        $this->insightlyclient->updateOrganisation($postedOrganisation);

        return new JsonResponse($project);
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
     * Gets the organisation linked to a project
     * @param Project $project
     * @return Organisation|null
     */
    private function getOrganisationByProject($project)
    {
        $organisation = null;

        if (!empty($project->getInsightlyProjectId())) {
            $insightyProject = $this->insightlyclient->getProject($project->getInsightlyProjectId());

            /** @var Link $link */
            $insightyLinks = $this->insightlyclient->getProjectLinks($insightyProject->getId());

            foreach ($insightyLinks as $insightyLink) {
                // One of the links is the organisation
                // This requires a refactor see: https://jira.uitdatabank.be/browse/PROJ-156
                if ($insightyLink->getOrganisationId()) {
                    $organisation = $this->insightlyclient->getOrganisation($insightyLink->getOrganisationId());
                }
            }
        }

        return $organisation;
    }

    /**
     * Returns a set of ids found on the objects in the provided array
     * @param array $data
     * @return array $ids
     */
    private function getIdsFromData($data)
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($data));
        $ids = [];

        foreach ($iterator as $key => $value) {
            if ($key === 'id') {
                $ids[] = $value;
            }
        }

        return $ids;
    }
}
