<?php

namespace CultuurNet\ProjectAanvraag\User;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    /**
     * @var UserRoleStorageInterface & MockObject
     */
    protected $userRoleStorage;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeed;

    public function setUp()
    {
        $this->userRoleStorage = $this->createMock(UserRoleStorageInterface::class);
        $this->cultureFeed = $this->createMock(\CultureFeed::class);
    }

    /**
     * Test UserService
     */
    public function testUserService()
    {
        $cfUser = new \CultureFeed_User();
        $cfUser->id = 1;

        $this->cultureFeed->expects($this->any())
            ->method('getUser')
            ->willReturn($cfUser);

        $this->userRoleStorage->expects($this->any())
            ->method('getRolesByUserId')
            ->willReturn(['administrator']);

        $userService = new UserService($this->cultureFeed, $this->userRoleStorage);
        $user = $userService->getUser(1);

        $this->assertInstanceOf(User::class, $user);
    }
}
