<?php

namespace CultuurNet\ProjectAanvraag\Entity;

use PHPUnit\Framework\TestCase;

/**
 * Tests the User entity.
 */
class UserTest extends TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $user = new User('my-first-id');
        $user->setId('my-id');
        $this->assertEquals('my-id', $user->getId());

        $user->setInsightylContactId('my-ins-id');
        $this->assertEquals('my-ins-id', $user->getInsightlyContactId());

        $user->setFirstName('my-first-name');
        $this->assertEquals('my-first-name', $user->getFirstName());

        $user->setLastName('my-last-name');
        $this->assertEquals('my-last-name', $user->getLastName());

        $user->setEmail('my-email');
        $this->assertEquals('my-email', $user->getEmail());

        $user->setNick('my-nick');
        $this->assertEquals('my-nick', $user->getNick());

        $json = [
            'id' => 'my-id',
            'insightlyContactId' => 'my-ins-id',
            'firstName' => 'my-first-name',
            'lastName' => 'my-last-name',
            'email' => 'my-email',
            'nick' => 'my-nick',
        ];
        $this->assertEquals($json, $user->jsonSerialize());
    }
}
