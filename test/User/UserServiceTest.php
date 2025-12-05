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
            $this->userRoleStorage,
            $this->cultureFeed,
            $this->platformClient
        );
    }

    /**
     * Test UiTiD v2
     */
    public function testUiTidV2Service()
    {
        $dummyToken = 'dummyToken';

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
