<?php

namespace CultuurNet\ProjectAanvraag\ErrorHandler;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use CultuurNet\ProjectAanvraag\Core\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class JsonErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var JsonErrorHandler
     */
    protected $errorHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->request = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->errorHandler = new JsonErrorHandler();
    }

    /**
     * Test exception handle
     */
    public function testHandleException()
    {
        $e = new \Exception('Some exception');
        $response = $this->handleException($e);

        $this->assertEquals($response, new JsonResponse($e->getMessage(), 500), 'It correctly handles the exception.');
    }

    /**
     * Test exception handle
     */
    public function testSkipHandleException()
    {
        $e = new \Exception('Some exception');
        $response = $this->handleException($e, false);

        $this->assertEquals($response, null, 'It correctly skips the handling of the exception.');
    }

    /**
     * Test exception handle
     */
    public function testHandleValidationException()
    {
        $e = new MissingRequiredFieldsException('message');
        $response = $this->handleException($e);

        $this->assertEquals($response, new JsonResponse($e->getMessage(), 400), 'It correctly handles the validation exception.');
    }

    /**
     * Test exception handle
     */
    public function testSkipHandleValidationException()
    {
        $e = new MissingRequiredFieldsException();
        $response = $this->handleException($e, false);

        $this->assertEquals($response, null, 'It correctly skips the handling of the validation exception.');
    }

    /**
     * Test Access Denied exception handle
     */
    public function testHandleAccessDeniedException()
    {
        $e = new AccessDeniedHttpException('message');
        $response = $this->handleException($e);

        $this->assertEquals($response, new JsonResponse($e->getMessage(), 403), 'It correctly handles the access denied exception.');
    }

    /**
     * Test skip Access Denied exception handle
     */
    public function testSkipHandleAccessDeniedException()
    {
        $e = new AccessDeniedHttpException();
        $response = $this->handleException($e, false);

        $this->assertEquals($response, null, 'It correctly skips the handling of the access denied exception.');
    }

    /**
     * @param \Exception $e
     * @param bool $isJsonRequest
     * @return null|JsonResponse
     */
    private function handleException(\Exception $e, $isJsonRequest = true)
    {
        $this->request
            ->expects($this->any())
            ->method('getAcceptableContentTypes')
            ->will($this->returnValue($isJsonRequest ? ['application/json'] : []));

        $handlers = [
            MissingRequiredFieldsException::class => 'handleValidationExceptions',
            AccessDeniedHttpException::class => 'handleAccessDeniedExceptions',
        ];

        $handler = !empty($handlers[get_class($e)]) ? $handlers[get_class($e)] : 'handleException';

        return  $this->errorHandler->{$handler}($e, $this->request);
    }
}
