<?php

namespace CultuurNet\ProjectAanvraag\User;

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
    protected $userRoleStorage;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeed;

    /*
     * @var string
     */
    private $platformHost;

    public function setUp()
    {
        $this->userRoleStorage = $this->createMock(UserRoleStorageInterface::class);
        $this->cultureFeed = $this->createMock(\CultureFeed::class);
        $this->platformHost = 'http://platform.example';
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

        $userService = new UserService(
            $this->cultureFeed,
            $this->userRoleStorage,
            $this->createMock(Session::class),
            $this->platformHost,
            $this->createMock(Client::class)
        );
        $user = $userService->getUser(1);

        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test UitIdV2
     */
    public function testUiTidV2Service()
    {
        $session = $this->createMock(Session::class);
        $client = $this->createMock(Client::class);
        $dummyToken = 'dummyToken';
        $request = $this->createMock(Request::class);

        $this->cultureFeed->expects($this->any())
            ->method('getUser')
            ->willThrowException(new \Exception());

        $this->userRoleStorage->expects($this->any())
            ->method('getRolesByUserId')
            ->willReturn(['administrator']);

        $session->expects($this->once())
            ->method('get')
            ->with('id_token')
            ->willReturn($dummyToken);

        $client->expects($this->once())
            ->method('get')
            ->with($this->platformHost . '/api/token/' . $dummyToken)
            ->willReturn(
                $request
            );

        $request->expects($this->once())
            ->method('send')
            ->willReturn(
                new Response(
                    200,
                    null,
                    json_encode(
                        [
                            'sub' => 'auth0|123',
                            'nickname' => 'NickN',
                        ]
                    )
                )
            );

        $userService = new UserService(
            $this->cultureFeed,
            $this->userRoleStorage,
            $session,
            $this->platformHost,
            $client
        );

        $user = $userService->getUser($dummyToken);

        $this->assertInstanceOf(User::class, $user);
    }
}
