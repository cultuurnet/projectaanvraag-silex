<?php

namespace CultuurNet\UiTIDProvider\User;

use CultuurNet\Auth\TokenCredentials;
use CultuurNet\Auth\User as MinimalUserInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UserSessionTest extends TestCase
{
    /**
     * @var MinimalUserInfo
     */
    protected $minimalUserInfo;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var UserSessionService
     */
    protected $service;

    /**
     * @var User
     */
    protected $user;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());
        $this->service = new UserSessionService($this->session);

        $id = 1;
        $credentials = new TokenCredentials('token', 'secret');
        $this->minimalUserInfo = new MinimalUserInfo($id, $credentials);

        $this->user = new User();
        $this->user->nick = 'foo_bar';
        $this->user->id = $id;
        $this->user->mbox = 'foo@bar.com';
    }

    /**
     * @test
     */
    public function it_can_store_and_retrieve_the_minimal_user_info()
    {
        $this->service->setMinimalUserInfo($this->minimalUserInfo);
        $this->assertEquals($this->minimalUserInfo, $this->service->getMinimalUserInfo());
    }

    /**
     * @test
     */
    public function it_returns_null_when_no_minimal_user_info_is_set()
    {
        $this->assertNull($this->service->getMinimalUserInfo());
    }

    /**
     * @test
     */
    public function it_can_store_and_retrieve_the_complete_user_object()
    {
        $this->service->setUser($this->user);
        $this->assertEquals($this->user, $this->service->getUser());
    }

    /**
     * @test
     */
    public function it_returns_null_when_no_complete_user_object_is_set()
    {
        $this->assertNull($this->service->getUser());
    }

    /**
     * @test
     */
    public function it_removes_the_minimal_user_info_when_logging_out()
    {
        $this->service->setMinimalUserInfo($this->minimalUserInfo);
        $this->service->logout();

        $this->assertNull($this->service->getMinimalUserInfo());
        $this->assertNull($this->service->getUser());
    }
}
