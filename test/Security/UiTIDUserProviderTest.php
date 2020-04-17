<?php

namespace CultuurNet\ProjectAanvraag\Security;

use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class UiTIDUserProviderTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserServiceInterface|MockObject
     */
    protected $userService;

    /**
     * @var UiTIDUserProvider
     */
    protected $userProvider;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->id = 1;
        $this->user->nick = 'foo';
        $this->user->city = 'Leuven';

        $this->userService = $this->createMock(UserServiceInterface::class);
        $this->userProvider = new UiTIDUserProvider($this->userService);
    }

    /**
     * @test
     */
    public function it_supports_only_uitid_users()
    {
        $this->assertTrue($this->userProvider->supportsClass(User::class));
        $this->assertFalse($this->userProvider->supportsClass(UserInterface::class));
    }

    /**
     * @test
     */
    public function it_queries_the_user_service_to_get_a_user_by_username()
    {
        $this->userService->expects($this->once())
            ->method('getUserByUsername')
            ->with($this->user->nick)
            ->willReturn($this->user);

        $this->assertEquals($this->user, $this->userProvider->loadUserByUsername($this->user->nick));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_a_user_can_not_be_loaded()
    {
        $this->userService->expects($this->once())
            ->method('getUserByUsername')
            ->with($this->user->nick)
            ->willReturn(null);

        $this->expectException(UsernameNotFoundException::class);
        $this->userProvider->loadUserByUsername($this->user->nick);
    }

    /**
     * @test
     */
    public function it_can_refresh_a_user()
    {
        $updatedUser = $this->user;
        $updatedUser->city = 'Herent';

        $this->userService->expects($this->once())
            ->method('getUserByUsername')
            ->with($this->user->nick)
            ->willReturn($updatedUser);

        $this->assertEquals(
            $updatedUser,
            $this->userProvider->refreshUser($this->user)
        );
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_refreshing_an_unsupported_user()
    {
        $user = $this->createMock(UserInterface::class);
        $this->expectException(UnsupportedUserException::class);
        $this->userProvider->refreshUser($user);
    }
}
