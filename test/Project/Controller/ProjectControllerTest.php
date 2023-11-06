<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Coupon\CouponValidatorInterface;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\PipelineStages;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\Project\ProjectService;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectControllerTest extends TestCase
{
    /**
     * @var ProjectController
     */
    private $controller;

    /**
     * @var MessageBusSupportingMiddleware & MockObject
     */
    private $messageBus;

    /**
     * @var ProjectServiceInterface & MockObject
     */
    private $projectService;

    /**
     * @var Request & MockObject
     */
    private $request;

    /**
     * @var AuthorizationCheckerInterface & MockObject
     */
    private $authorizationChecker;

    /**
     * @var InsightlyClientInterface & MockObject
     */
    private $legacyInsightlyClient;

    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var CouponValidatorInterface & MockObject
     */
    private $couponValidator;

    /**
     * @var \stdClass
     */
    private $formData;

    public function setUp()
    {
        $this->messageBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->projectService = $this->createMock(ProjectService::class);

        $this->request = $this->createMock(Request::class);

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->legacyInsightlyClient = $this->createMock(InsightlyClientInterface::class);

        $this->insightlyClient = new InsightlyClient(
            $this->createMock(ClientInterface::class),
            'api_key',
            new PipelineStages([])
        );

        $this->couponValidator = $this->createMock(CouponValidatorInterface::class);

        $this->controller = new ProjectController(
            $this->messageBus,
            $this->projectService,
            $this->authorizationChecker,
            $this->couponValidator,
            $this->legacyInsightlyClient,
            $this->insightlyClient,
            false
        );

        $this->formData = new \stdClass();
        $this->formData->name = 'name';
        $this->formData->summary = 'summary';
        $this->formData->integrationType = 2;
        $this->formData->termsAndConditions = 'termsAndConditions';
    }

    /**
     * Test createProject
     */
    public function testCreateProject()
    {
        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($this->formData));

        $this->couponValidator->expects($this->never())
            ->method('validateCoupon');

        $createProject = new CreateProject($this->formData->name, $this->formData->summary, $this->formData->integrationType);
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($createProject);

        $response = $this->controller->createProject($this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }

    /**
     * Test createProject with coupon
     */
    public function testCreateProjectWithCoupon()
    {
        $formData = $this->formData;
        $formData->coupon = 'coupon';

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->willReturn(json_encode($this->formData));

        $this->couponValidator->expects($this->once())
            ->method('validateCoupon');

        $createProject = new CreateProject($this->formData->name, $this->formData->summary, $this->formData->integrationType, $this->formData->coupon);
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($createProject);

        $response = $this->controller->createProject($this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }

    public function testCreateProjectException()
    {
        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(''));

        $this->expectException(MissingRequiredFieldsException::class);

        $this->controller->createProject($this->request);
    }

    /**
     * Test getProjects
     */
    public function testGetProjects()
    {
        $query = new ParameterBag(
            [
                'name' => '',
                'start' => 0,
                'max' => 10,
            ]
        );

        $this->request->query = $query;

        $result = [
            'total' => 0,
            'results' => [],
        ];

        $this->projectService
            ->expects($this->once())
            ->method('searchProjects')
            ->with(0, 10, '')
            ->willReturn($result);

        $response = $this->controller->getProjects($this->request);
        $this->assertEquals(new JsonResponse($result), $response, 'It correctly searches the projects');
    }

    /**
     * Test getProject
     */
    public function testGetProject()
    {
        $project = $this->setupProjectTest('view');
        $response = $this->controller->getProject(1);
        $this->assertEquals(new JsonResponse($project), $response, 'It correctly fetches the project');
    }

    public function testGetProjectAccessDeniedException()
    {
        $this->setupProjectTest('view', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->getProject(1);
    }

    public function testGetProjectNotFoundHttpException()
    {
        $this->projectService
            ->expects($this->any())
            ->method('loadProject')
            ->willReturn(null);

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->getProject(1);
    }

    /**
     * Test deleteProject
     */
    public function testDeleteProject()
    {
        $project = $this->setupProjectTest('edit');
        $deleteProject = new DeleteProject($project);

        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($deleteProject);

        $response = $this->controller->deleteProject(1);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }

    public function testDeleteProjectException()
    {
        $this->setupProjectTest('edit', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->deleteProject(1);
    }

    /**
     * Test blockProject
     */
    public function testBlockProject()
    {
        $project = $this->setupProjectTest('block');
        $blockProject = new BlockProject($project);

        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($blockProject);

        $response = $this->controller->blockProject(1);
        $this->assertEquals(new JsonResponse($project), $response, 'It correctly handles the request');
    }

    public function testBlockProjectException()
    {
        $this->setupProjectTest('block', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->blockProject(1);
    }

    /**
     * Test activateProject
     */
    public function testActivateProject()
    {
        $project = $this->setupProjectTest('activate');
        $activateProject = new ActivateProject($project);
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($activateProject);

        $response = $this->controller->activateProject(1);
        $this->assertEquals(new JsonResponse($project), $response, 'It correctly handles the request');
    }

    public function testActivateProjectException()
    {
        $this->setupProjectTest('activate', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->activateProject(1);
    }

    /**
     * Test requestActivation with a coupon.
     */
    public function testRequestActivationWithoutCoupon()
    {
        $project = $this->setupProjectTest('edit');
        $postData = [
            'name' => 'name',
            'street' => 'street and number',
            'postal' => 'postal',
            'city' => 'city',
            'vat' => 'VAT',
            'email' => 'payment',
        ];
        $request = Request::create('/', 'POST', [], [], [], [], json_encode($postData));

        $address = new Address($postData['street'], $postData['postal'], $postData['city']);
        $requestActivation = new RequestActivation($project, 'name', $address, 'VAT', 'payment');
        $this->messageBus
            ->expects($this->any())
            ->method('handle')
            ->with($requestActivation);

        $response = $this->controller->requestActivation(1, $request);

        $this->assertEquals(new JsonResponse($project), $response, 'It correctly handles the request');
    }

    /**
     * Test requestActivation with a coupon.
     */
    public function testRequestActivationWithCoupon()
    {
        $project = $this->setupProjectTest('edit');
        $postData = [
            'coupon' => 'test',
        ];
        $request = Request::create('/', 'POST', [], [], [], [], json_encode($postData));

        $activateProject = new ActivateProject($project, 'test');
        $this->messageBus
            ->expects($this->any())
            ->method('handle')
            ->with($activateProject);

        $response = $this->controller->requestActivation(1, $request);

        $this->assertEquals(new JsonResponse($project), $response, 'It correctly handles the request');
    }

    public function testRequestActivationException()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->requestActivation(1, $request);
    }

    /**
     * Test activateProject
     */
    public function testUpdateContentFilter()
    {
        $project = $this->setupProjectTest('edit');

        $postData = [
            'contentFilter' => 'test',
        ];
        $request = Request::create('/', 'POST', [], [], [], [], json_encode($postData));

        $this->projectService->expects($this->once())
            ->method('updateContentFilter')
            ->with($project, 'test');

        $response = $this->controller->updateContentFilter($request, 1);

        $this->assertEquals(new JsonResponse($project), $response, 'It correctly handles the request');
    }

    public function testUpdateContentFilterException()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->updateContentFilter($request, 1);
    }

    public function testUpdateContentFilterRequiredFields()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit');

        $this->expectException(MissingRequiredFieldsException::class);

        $this->controller->updateContentFilter($request, 1);
    }

    /**
     * Test getOrganisation
     */
    public function testGetOrganisation()
    {
        $project = new Project();
        $project->setId(1);
        $project->setInsightlyProjectId(2);
        $project->setCreated(new \DateTime());
        $project->setUpdated(new \DateTime());

        $insightlyProject = new \CultuurNet\ProjectAanvraag\Insightly\Item\Project();
        $insightlyProject->setId(2);
        $link = new Link();
        $link->setOrganisationId(3);
        $insightlyProject->addLink($link);

        $organisation = new Organisation();
        $organisation->setName('name');

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProject')
            ->with(2)
            ->willReturn($insightlyProject);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProjectLinks')
            ->with(2)
            ->willReturn([$link]);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getOrganisation')
            ->with(3)
            ->willReturn($organisation);

        $this->projectService
            ->expects($this->once())
            ->method('loadProject')
            ->with(1)
            ->willReturn($project);

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->with('edit', $project)
            ->willReturn(true);

        $response = $this->controller->getOrganisation(1);

        $this->assertEquals(new JsonResponse($organisation), $response, 'It correctly fetches the organisation');
    }

    public function testGetOrganisationNotFound()
    {
        $this->setupProjectTest('edit', true);

        $this->expectException(NotFoundHttpException::class);

        $this->controller->getOrganisation(1);
    }

    /**
     * Test the updating of an organisation.
     */
    public function testUpdateOrganisation()
    {
        $project = new Project();
        $project->setId(1);
        $project->setInsightlyProjectId(2);
        $project->setCreated(new \DateTime());
        $project->setUpdated(new \DateTime());

        $insightlyProject = new \CultuurNet\ProjectAanvraag\Insightly\Item\Project();
        $insightlyProject->setId(2);
        $link = new Link();
        $link->setOrganisationId(3);
        $insightlyProject->addLink($link);

        $organisation = new Organisation();
        $organisation->setId(95591403);
        $organisation->setName('name');
        $address = new \CultuurNet\ProjectAanvraag\Insightly\Item\Address();
        $address->setId(48270160);
        $organisation->setAddresses([$address]);

        $contactInfo = new Contact();
        $contactInfo->setId(102388049);
        $organisation->setContactInfo([$contactInfo]);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProject')
            ->with(2)
            ->willReturn($insightlyProject);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProjectLinks')
            ->with(2)
            ->willReturn([$link]);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getOrganisation')
            ->with(3)
            ->willReturn($organisation);

        $this->projectService
            ->expects($this->once())
            ->method('loadProject')
            ->with(1)
            ->willReturn($project);

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->with('edit', $project)
            ->willReturn(true);

        $postData = file_get_contents(__DIR__ . '/../data/update_organisation.json');
        $request = Request::create('/', 'PUT', [], [], [], [], $postData);

        $response = $this->controller->updateOrganisation(1, $request);
        $this->assertEquals(new JsonResponse($project), $response, 'It correctly updates the organisation');
    }

    public function testUpdateOrganisationIdValidation()
    {
        $project = new Project();
        $project->setId(1);
        $project->setInsightlyProjectId(2);
        $project->setCreated(new \DateTime());
        $project->setUpdated(new \DateTime());

        $insightlyProject = new \CultuurNet\ProjectAanvraag\Insightly\Item\Project();
        $insightlyProject->setId(2);
        $link = new Link();
        $link->setOrganisationId(3);
        $insightlyProject->addLink($link);

        $organisation = new Organisation();
        $organisation->setId(955914032);
        $organisation->setName('name');
        $address = new \CultuurNet\ProjectAanvraag\Insightly\Item\Address();
        $address->setId(482701602);
        $organisation->setAddresses([$address]);

        $contactInfo = new Contact();
        $contactInfo->setId(1023880549);
        $organisation->setContactInfo([$contactInfo]);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProject')
            ->with(2)
            ->willReturn($insightlyProject);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getProjectLinks')
            ->with(2)
            ->willReturn([$link]);

        $this->legacyInsightlyClient
            ->expects($this->once())
            ->method('getOrganisation')
            ->with(3)
            ->willReturn($organisation);

        $this->projectService
            ->expects($this->once())
            ->method('loadProject')
            ->with(1)
            ->willReturn($project);

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->with('edit', $project)
            ->willReturn(true);

        $postData = file_get_contents(__DIR__ . '/../data/update_organisation.json');
        $request = Request::create('/', 'PUT', [], [], [], [], $postData);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->updateOrganisation(1, $request);
    }

    public function testUpdateOrganisationValidateFields()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', true);

        $this->expectException(MissingRequiredFieldsException::class);

        $this->controller->updateOrganisation(1, $request);
    }

    public function testUpdateOrganisationAccessDenied()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', false);

        $this->expectException(AccessDeniedHttpException::class);

        $this->controller->updateOrganisation(1, $request);
    }

    /**
     * Setup a project update test.
     * Test if the access check is done and return the given value.
     * @return Project
     */
    private function setupProjectTest($operation, $returnValue = true)
    {
        $project = $this->createMock(ProjectInterface::class);

        $this->projectService
            ->expects($this->any())
            ->method('loadProject')
            ->with(1)
            ->willReturn($project);

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->with($operation, $project)
            ->willReturn($returnValue);

        $this->messageBus
            ->expects($this->any())
            ->method('handle');

        return $project;
    }
}
