<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\ProjectAanvraag\JsonAssertionTrait;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    use JsonAssertionTrait;

    /**
     * Test parsing of a culturefeed user to User
     */
    public function testCultureFeedUserParsing()
    {
        $cultureFeedUser = new \CultureFeed_User();
        $cultureFeedUser->id = 123;
        $cultureFeedUser->familyName = 'familyName';
        $cultureFeedUser->nick = 'nickname';
        $cultureFeedUser->givenName = 'firstName';

        $user = User::fromCultureFeedUser($cultureFeedUser);

        $this->assertInstanceOf(User::class, $user);

        $this->assertEquals('123', $user->id, 'The id is correctly parsed');
        $this->assertEquals('nickname', $user->nick, 'The nick is correctly parsed');
        $this->assertEquals('familyName', $user->familyName, 'The familyName is correctly parsed');
        $this->assertEquals('firstName', $user->givenName, 'The givenName is correctly parsed');
    }


    /**
     * Test parsing of a culturefeed user to User
     */
    public function testCultureFeedUserSerialize()
    {
        $user = new User();
        $user->id = 'id';
        $user->familyName = 'familyName';
        $user->nick = 'nickname';
        $user->givenName = 'firstName';
        $user->setRoles(['uitid_user']);
        $user->preferredLanguage = 'nl';
        $user->mbox = 'x@x.com';
        $user->mboxVerified = true;
        $user->dob = 483228000;
        $user->depiction = 'depiction';
        $user->zip = 'zipcode';
        $user->city = 'City';
        $user->country = 'BE';
        $user->gender = 'male';
        $user->hasChildren = true;

        $this->assertJsonEquals(json_encode($user), 'User/data/serialized/user.json');
    }

    /**
     * Test if the role setters are working.
     */
    public function testRoleSettersAndGetters()
    {
        $user = new User();

        $roles = [User::USER_ROLE_ADMINISTRATOR];

        $user->setRoles($roles);
        $this->assertEquals($roles, $user->getRoles(), 'It correctly sets and gets the roles');
    }

    /**
     * Test if the correct admin status is returned.
     */
    public function testAdminStatus()
    {
        $user = new User();
        $this->assertFalse($user->isAdmin(), 'The user is correctly seen as no admin');

        $user->setRoles([User::USER_ROLE_ADMINISTRATOR]);
        $this->assertTrue($user->isAdmin(), 'The user is correctly seen as admin');

        $user->setRoles(['test']);
        $this->assertFalse($user->isAdmin(), 'The user is correctly seen as no admin');
    }
}
