<?php

namespace CultuurNet\ProjectAanvraag\Auth;

use CultuurNet\Auth\ConsumerCredentials;
use CultuurNet\Auth\TokenCredentials;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class AuthServiceTest extends TestCase
{
    /**
     * @var TokenCredentials
     */
    protected $requestToken;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var AuthService
     */
    protected $service;

    protected function setUp(): void
    {
        $this->session = new Session(new MockArraySessionStorage());

        $this->requestToken = new TokenCredentials('token', 'secret');

        $this->service = new AuthService(
            'http://example.com',
            new ConsumerCredentials('key', 'secret'),
            $this->session
        );
    }

    /**
     * @test
     */
    public function it_can_store_retrieve_and_remove_the_request_token()
    {
        // Request token is null by default.
        $this->assertNull($this->service->getStoredRequestToken());

        // Store an actual request token.
        $this->service->storeRequestToken($this->requestToken);

        // Make sure we get the same request token back.
        $this->assertEquals(
            $this->requestToken,
            $this->service->getStoredRequestToken()
        );

        // Remove the request token.
        $this->service->removeStoredRequestToken();

        // Make sure the request token is null again.
        $this->assertNull($this->service->getStoredRequestToken());
    }
}
