<?php

namespace CultuurNet\UiTIDProvider\Security;

use CultuurNet\UiTIDProvider\User\User;
use CultuurNet\UiTIDProvider\User\UserServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UiTIDAuthenticatorTest extends TestCase
{
    /**
     * @var UiTIDToken
     */
    protected $unauthenticatedToken;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserServiceInterface|MockObject
     */
    protected $userService;

    /**
     * @var UiTIDAuthenticator
     */
    protected $authenticator;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->id = 1;
        $this->user->nick = 'foo';

        $this->unauthenticatedToken = new UiTIDToken();
        $this->unauthenticatedToken->setUser('1');

        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->authenticator = new UiTIDAuthenticator($this->userService);
    }

    /**
     * @test
     */
    public function it_only_supports_uitid_tokens()
    {
        $token = new UiTIDToken();
        $this->assertTrue($this->authenticator->supports($token));

        $token = new AnonymousToken('key', 'user');
        $this->assertFalse($this->authenticator->supports($token));
    }

    /**
     * @test
     */
    public function it_can_authenticate_a_uitid_token()
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->with('1')
            ->willReturn($this->user);

        $authenticated = $this->authenticator->authenticate($this->unauthenticatedToken);

        $expected = new UiTIDToken($this->user->getRoles());
        $expected->setUser($this->user);

        $this->assertEquals($expected, $authenticated);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_user_does_not_exist()
    {
        $this->userService->expects($this->once())
            ->method('getUser')
            ->with('1')
            ->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->authenticator->authenticate($this->unauthenticatedToken);
    }
}
