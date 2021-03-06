<?php

namespace CultuurNet\ProjectAanvraag\ErrorHandler;

use CultuurNet\ProjectAanvraag\Core\Exception\MissingRequiredFieldsException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class JsonErrorHandlerTest extends TestCase
{
    /**
     * @var Request & MockObject
     */
    protected $request;

    /**
     * @var JsonErrorHandler
     */
    protected $errorHandler;

    public function setUp()
    {
        $this->request = $this->createMock(Request::class);

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

        $this->assertEquals(null, $response, 'It correctly skips the handling of the exception.');
    }

    /**
     * Test exception handle
     */
    public function testHandleValidationException()
    {
        $e = new MissingRequiredFieldsException('message');
        $response = $this->handleException($e);

        $data = [
            'code' => MissingRequiredFieldsException::ERROR_CODE,
            'message' => 'message',
        ];
        $this->assertEquals($response, new JsonResponse($data, 400), 'It correctly handles the validation exception.');
    }

    /**
     * Test exception handle
     */
    public function testSkipHandleValidationException()
    {
        $e = new MissingRequiredFieldsException();
        $response = $this->handleException($e, false);

        $this->assertEquals(null, $response, 'It correctly skips the handling of the validation exception.');
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

        $this->assertEquals(null, $response, 'It correctly skips the handling of the access denied exception.');
    }

    /**
     * Test Not found exception handle
     */
    public function testHandleNotFoundException()
    {
        $e = new NotFoundHttpException('message');
        $response = $this->handleException($e);

        $this->assertEquals($response, new JsonResponse($e->getMessage(), 404), 'It correctly handles the not found exception.');
    }

    /**
     * Test skip Not found exception handle
     */
    public function testSkipHandleNotFoundException()
    {
        $e = new NotFoundHttpException();
        $response = $this->handleException($e, false);

        $this->assertEquals(null, $response, 'It correctly skips the handling of the not found exception.');
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
            NotFoundHttpException::class => 'handleNotFoundExceptions',
            MissingRequiredFieldsException::class => 'handleValidationExceptions',
            AccessDeniedHttpException::class => 'handleAccessDeniedExceptions',
        ];

        $handler = !empty($handlers[get_class($e)]) ? $handlers[get_class($e)] : 'handleException';

        return  $this->errorHandler->{$handler}($e, $this->request);
    }
}
