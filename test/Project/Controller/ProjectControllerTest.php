<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Address;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\ActivateProject;
use CultuurNet\ProjectAanvraag\Project\Command\BlockProject;
use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Command\DeleteProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectController
     */
    protected $controller;

    /**
     * @var MessageBusSupportingMiddleware|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageBus;

    /**
     * @var ProjectServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $projectService;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationChecker;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->messageBus = $this
            ->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')
            ->disableOriginalConstructor()
            ->getMock();

        $this->projectService = $this
            ->getMockBuilder('CultuurNet\ProjectAanvraag\Project\ProjectService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->authorizationChecker = $this
            ->getMockBuilder('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface')
            ->getMock();

        $this->controller = new ProjectController($this->messageBus, $this->projectService, $this->authorizationChecker);
    }

    /**
     * Test createProject
     */
    public function testCreateProject()
    {
        $content = file_get_contents(__DIR__ . '/../data/add_project_form_data.json');
        $json = json_decode($content);

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        $createProject = new CreateProject($json->name, $json->summary, $json->integrationType);
        $this->messageBus
            ->expects($this->once())
            ->method('handle')
            ->with($createProject);

        $response = $this->controller->createProject($this->request);
        $this->assertEquals(new JsonResponse(), $response, 'It correctly handles the request');
    }

    /**
     * Test createProject exception
     * @expectedException \CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException
     */
    public function testCreateProjectException()
    {
        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue(''));

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
            ->will($this->returnValue($result));

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

    /**
     * Test getProject AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testGetProjectAccessDeniedException()
    {
        $this->setupProjectTest('view', false);
        $this->controller->getProject(1);
    }

    /**
     * Test getProject NotFoundHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testGetProjectNotFoundHttpException()
    {
        $this->projectService
            ->expects($this->any())
            ->method('loadProject')
            ->will($this->returnValue(null));

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValue(true));

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

    /**
     * Test deleteProject AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testDeleteProjectException()
    {
        $this->setupProjectTest('edit', false);
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

    /**
     * Test blockProject AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testBlockProjectException()
    {
        $this->setupProjectTest('block', false);
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

    /**
     * Test activateProject AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testActivateProjectException()
    {
        $this->setupProjectTest('activate', false);
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
            'email' => 'email',
            'street' => 'street',
            'number' => 'number',
            'postal' => 'postal',
            'city' => 'city',
            'identifier' => 'VAT',
        ];
        $request = Request::create('/', 'POST', [], [], [], [], json_encode($postData));

        $address = new Address($postData['street'], $postData['number'], $postData['postal'], $postData['city']);
        $requestActivation = new RequestActivation($project, 'email', 'name', $address, 'VAT');
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

    /**
     * Test requestActivation AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testRequestActivationException()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', false);
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

    /**
     * Test requestActivation AccessDeniedHttpException
     * @expectedException \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function testUpdateContentFilterException()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit', false);

        $this->controller->updateContentFilter($request, 1);
    }

    /**
     * Test requestActivation MissingRequiredFieldsException
     * @expectedException \CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException
     */
    public function testUpdateContentFilterRequiredFields()
    {
        $request = Request::create('/');
        $this->setupProjectTest('edit');

        $this->controller->updateContentFilter($request, 1);
    }

    /**
     * Setup a project update test.
     * Test if the access check is done and return the given value.
     * @return Project
     */
    private function setupProjectTest($operation, $returnValue = true)
    {
        $project = $this->getMock(ProjectInterface::class);

        $this->projectService
            ->expects($this->any())
            ->method('loadProject')
            ->with(1)
            ->will($this->returnValue($project));

        $this->authorizationChecker
            ->expects($this->any())
            ->method('isGranted')
            ->with($operation, $project)
            ->will($this->returnValue($returnValue));

        $this->messageBus
            ->expects($this->any())
            ->method('handle');

        return $project;
    }
}
