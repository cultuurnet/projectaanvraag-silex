<?php

namespace CultuurNet\UiTIDProvider\Security;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User as MinimalUserInfo;
use CultuurNet\UiTIDProvider\User\User;
use CultuurNet\UiTIDProvider\User\UserSessionService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UiTIDListenerTest extends TestCase
{
    /**
     * @var AuthenticationManagerInterface|MockObject
     */
    protected $authenticationManager;

    /**
     * @var TokenStorageInterface|MockObject
     */
    protected $tokenStorage;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var UserSessionService
     */
    protected $userSessionService;

    /**
     * @var MinimalUserInfo
     */
    protected $minimalUserInfo;

    /**
     * @var UiTIDToken
     */
    protected $minimalToken;

    /**
     * @var GetResponseEvent|MockObject
     */
    protected $event;

    /**
     * @var UiTIDListener
     */
    protected $listener;

    protected function setUp(): void
    {
        $this->authenticationManager = $this->createMock(AuthenticationManagerInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);

        $this->session = new Session(new MockArraySessionStorage());
        $this->userSessionService = new UserSessionService($this->session);

        $this->minimalUserInfo = new MinimalUserInfo(1, new TokenCredentials('token', 'secret'));

        $this->minimalToken = new UiTIDToken();
        $this->minimalToken->setUser((string) $this->minimalUserInfo->getId());

        $this->event = $this->getMockBuilder(GetResponseEvent::class)
            ->setConstructorArgs([
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                HttpKernelInterface::MASTER_REQUEST,
            ])->getMock();

        $this->listener = new UiTIDListener(
            $this->authenticationManager,
            $this->tokenStorage,
            $this->userSessionService
        );
    }

    /**
     * @test
     */
    public function it_denies_access_when_no_minimal_user_info_is_found()
    {
        // Makes sure that a Response object with status code 401 is passed to
        // setResponse().
        // Don't use with(new Response(...)) as responses contain datetime
        // information that may not be the same as the actual response, which
        // may cause random failing tests.
        $this->event->expects($this->once())
            ->method('setResponse')
            ->will($this->returnCallback(function ($response) {
                /* @var Response $response */
                $this->assertInstanceOf(Response::class, $response);
                $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
            }));

        $this->listener->handle($this->event);
    }

    /**
     * @test
     */
    public function it_denies_access_when_the_user_does_not_exist()
    {
        $this->userSessionService->setMinimalUserInfo($this->minimalUserInfo);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->with($this->minimalToken)
            ->willThrowException(new AuthenticationException());

        // Makes sure that a Response object with status code 401 is passed to
        // setResponse().
        // Don't use with(new Response(...)) as responses contain datetime
        // information that may not be the same as the actual response, which
        // may cause random failing tests.
        $this->event->expects($this->once())
            ->method('setResponse')
            ->will($this->returnCallback(function ($response) {
                /* @var Response $response */
                $this->assertInstanceOf(Response::class, $response);
                $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
            }));

        $this->listener->handle($this->event);
    }

    /**
     * @test
     */
    public function it_grants_access_when_authenticated()
    {
        $this->userSessionService->setMinimalUserInfo($this->minimalUserInfo);

        $user = new User();
        $user->id = $this->minimalUserInfo->getId();

        $authToken = new UiTIDToken($user->getRoles());
        $authToken->setUser($user);

        $this->authenticationManager->expects($this->once())
            ->method('authenticate')
            ->with($this->minimalToken)
            ->willReturn($authToken);

        $this->tokenStorage->expects($this->once())
            ->method('setToken')
            ->with($authToken);

        // Make sure no Response is set, so the request can be handled by the
        // actual controllers.
        $this->event->expects($this->never())
            ->method('setResponse');

        $this->listener->handle($this->event);
    }
}
