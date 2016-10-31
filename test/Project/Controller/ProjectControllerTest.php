<?php

namespace CultuurNet\ProjectAanvraag\Project\Controller;

use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        $this->request
            ->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($content));

        $this->messageBus
            ->expects($this->any())
            ->method('handle');

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

        $this->messageBus
            ->expects($this->any())
            ->method('handle');

        $this->controller->createProject($this->request);
    }
}
