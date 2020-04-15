<?php

namespace CultuurNet\UiTIDProvider\Security;

use PHPUnit\Framework\TestCase;

class UiTIDTokenTest extends TestCase
{
    /**
     * @test
     */
    public function it_always_returns_an_empty_string_as_credentials()
    {
        $token = new UiTIDToken();
        $this->assertEmpty($token->getCredentials());
    }

    /**
     * @test
     */
    public function it_is_only_authenticated_when_at_least_one_role_is_set()
    {
        $token = new UiTIDToken();
        $this->assertFalse($token->isAuthenticated());

        $token = new UiTIDToken(array('TEST_ROLE'));
        $this->assertTrue($token->isAuthenticated());
    }
}
