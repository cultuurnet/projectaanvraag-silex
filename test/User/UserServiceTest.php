<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\ProjectAanvraag\Platform\PlatformClient;
use CultuurNet\ProjectAanvraag\Platform\PlatformClientInterface;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;
use Guzzle\Http\Message\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;

class UserServiceTest extends TestCase
{
    /**
     * @var UserRoleStorageInterface & MockObject
     */
    private $userRoleStorage;

    /**
     * @var \CultureFeed & MockObject
     */
    private $cultureFeed;

    /**
     * @var PlatformClientInterface & MockObject
     */
    private $platformClient;

    /**
     * @var UserService
     */
    private $userService;

    public function setUp()
    {
        $this->userRoleStorage = $this->createMock(UserRoleStorageInterface::class);
        $this->cultureFeed = $this->createMock(\CultureFeed::class);
        $this->platformClient = $this->createMock(PlatformClientInterface::class);

        $this->userService = new UserService(
            $this->cultureFeed,
            $this->userRoleStorage,
            $this->platformClient
        );
    }

    /**
     * Test UiTiD v1
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

        $this->platformClient->expects($this->never())
            ->method('getCurrentUser');

        $user = $this->userService->getUser(1);

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test UiTiD v2
     */
    public function testUiTidV2Service()
    {
        $dummyToken = 'dummyToken';

        $this->cultureFeed->expects($this->any())
            ->method('getUser')
            ->willThrowException(new \Exception());

        $this->userRoleStorage->expects($this->any())
            ->method('getRolesByUserId')
            ->willReturn(['administrator']);

        $this->platformClient->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(
                [
                    'sub' => 1,
                    'nickname' => 'test',
                ]
            );

        $user = $this->userService->getUser($dummyToken);

        $this->assertInstanceOf(User::class, $user);
    }
}
