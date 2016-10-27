<?php

namespace CultuurNet\ProjectAanvraag\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parsing of a culturefeed user to User
     */
    public function testCultureFeedUserParsing()
    {
        $cultureFeedUser = new \CultureFeed_User();
        $cultureFeedUser->id = 123;

        $user = User::fromCultureFeedUser($cultureFeedUser);
        $user->setRoles(['administrator']);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($user->id, '123', 'The id is correctly parsed');
        $this->assertEquals($user->getRoles(), ['administrator'], 'It correctly returns the roles');
        $this->assertEquals($user->hasRole('administrator'), true, 'It correctly checks the user for a given role');
    }
}
