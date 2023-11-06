<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Coupon\CouponValidatorInterface;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Parser\OrganisationParser;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\CustomFieldNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\CustomFieldSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers\OrganizationSerializer;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class ProjectController
{
    use validateRequiredFieldsTraits;

    /**
     * @var MessageBusSupportingMiddleware
     */
    private $commandBus;

    /**
     * @var ProjectServiceInterface
     */
    private $projectService;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var CouponValidatorInterface
     */
    private $couponValidator;

    /**
     * @var InsightlyClientInterface
     */
    private $legacyInsightlyClient;

    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var bool
     */
    private $useNewInsightlyInstance;

    public function __construct(
        MessageBusSupportingMiddleware $commandBus,
        ProjectServiceInterface $projectService,
        AuthorizationCheckerInterface $authorizationChecker,
        CouponValidatorInterface $couponValidator,
        InsightlyClientInterface $legacyInsightlyClient,
        InsightlyClient  $insightlyClient,
        bool $useNewInsightlyInstance
    ) {
        $this->commandBus = $commandBus;
        $this->projectService = $projectService;
        $this->authorizationChecker = $authorizationChecker;
        $this->couponValidator = $couponValidator;
        $this->legacyInsightlyClient = $legacyInsightlyClient;
        $this->insightlyClient = $insightlyClient;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
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
        if (!$this->useNewInsightlyInstance) {
            $this->legacyInsightlyClient->updateOrganisation($postedOrganisation);
        } else {
            $organizationAsArray = $postedOrganisation->toInsightly();

            // Transforming the legacy format of custom fields to the new format.
            $customFields = [];
            $customFieldSerializer = new CustomFieldSerializer();
            try {
                // Email is required.
                $customFields[] = $customFieldSerializer->createCustomField(
                    CustomFieldSerializer::CUSTOM_FIELD_EMAIL,
                    $customFieldSerializer->getCustomFieldValue($organizationAsArray['CUSTOMFIELDS'], 'ORGANISATION_FIELD_2')
                );

                // Tax is optional and can throw an CustomFieldNotFound exception.
                $customFields[] = $customFieldSerializer->createCustomField(
                    CustomFieldSerializer::CUSTOM_FIELD_TAX_NUMBER,
                    $customFieldSerializer->getCustomFieldValue($organizationAsArray['CUSTOMFIELDS'], 'ORGANISATION_FIELD_1')
                );
            } catch (CustomFieldNotFound $customFieldNotFound) {
            }
            $organizationAsArray['CUSTOMFIELDS'] = $customFields;

            $this->insightlyClient->organizations()->update(
                (new OrganizationSerializer())->fromInsightlyArray($organizationAsArray)
            );
        }

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
     * Gets the organisation linked to a project
     * @param Project $project
     * @return Organisation|null
     */
    private function getOrganisationByProject($project)
    {
        if (!$this->useNewInsightlyInstance) {
            if (empty($project->getInsightlyProjectId())) {
                return null;
            }

            $insightlyProject = $this->legacyInsightlyClient->getProject($project->getInsightlyProjectId());

            /** @var Link $link */
            $insightlyLinks = $this->legacyInsightlyClient->getProjectLinks($insightlyProject->getId());

            $organisation = null;
            foreach ($insightlyLinks as $insightlyLink) {
                // One of the links is the organisation
                // This requires a refactor see: https://jira.uitdatabank.be/browse/PROJ-156
                if ($insightlyLink->getOrganisationId()) {
                    $organisation = $this->legacyInsightlyClient->getOrganisation($insightlyLink->getOrganisationId());
                }
            }

            return $organisation;
        }

        if ($project->getProjectIdInsightly()) {
            $organizationId = $this->insightlyClient->projects()->getLinkedOrganizationId(new Id($project->getProjectIdInsightly()));
            $organization = $this->insightlyClient->organizations()->getById($organizationId);

            $parsedOrganization = OrganisationParser::parseToResult((new OrganizationSerializer())->toInsightlyArray($organization));
            if ($organization->getTaxNumber()) {
                $parsedOrganization->addCustomField('ORGANISATION_FIELD_1', $organization->getTaxNumber()->getValue());
            }
            $parsedOrganization->addCustomField('ORGANISATION_FIELD_2', $organization->getEmail()->getValue());

            return $parsedOrganization;
        }

        return null;
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
