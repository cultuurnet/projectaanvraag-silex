<?php

namespace CultuurNet\UiTIDProvider\User;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedUserServiceTest extends TestCase
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var UserServiceInterface|MockObject
     */
    protected $apiService;

    /**
     * @var CachedUserService
     */
    protected $cachedService;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->user->id = 1;
        $this->user->nick = 'foo_bar';
        $this->user->mbox = 'foo@bar.com';

        $this->apiService = $this->createMock(UserServiceInterface::class);

        $this->cachedService = new CachedUserService(
            $this->apiService
        );
    }

    /**
     * @test
     */
    public function it_returns_a_user_from_cache_if_possible()
    {
        $this->cachedService->cacheUser($this->user);

        $this->assertEquals(
            $this->user,
            $this->cachedService->getUser($this->user->id)
        );

        $this->assertEquals(
            $this->user,
            $this->cachedService->getUserByUsername($this->user->getUsername())
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_another_user_service_when_a_user_is_not_cached_by_id()
    {
        // The API service only expects one call.
        $this->apiService->expects($this->once())
            ->method('getUser')
            ->with($this->user->id)
            ->willReturn($this->user);

        // Get the user before he is cached.
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUser($this->user->id)
        );

        // Make sure the user is cached now. Not only by id but also by
        // username.
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUser($this->user->id)
        );
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUserByUsername($this->user->nick)
        );
    }

    /**
     * @test
     */
    public function it_delegates_to_another_user_service_when_a_user_is_not_cached_by_username()
    {
        // The API service only expects one call.
        $this->apiService->expects($this->once())
            ->method('getUserByUsername')
            ->with($this->user->nick)
            ->willReturn($this->user);

        // Get the user before he is cached.
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUserByUsername($this->user->nick)
        );

        // Make sure the user is cached now. Not only by username but also by
        // id.
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUserByUsername($this->user->nick)
        );
        $this->assertEquals(
            $this->user,
            $this->cachedService->getUser($this->user->id)
        );
    }
}
