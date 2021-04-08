<?php

namespace CultuurNet\ProjectAanvraag\Voter;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProjectVoterTest extends TestCase
{
    /**
     * @var TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $token;

    /**
     * @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

    /**
     * @var ProjectVoter
     */
    protected $voter;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->token = $this->createMock(TokenInterface::class);

        $this->project = $this->createMock(ProjectInterface::class);

        $this->voter = new ProjectVoter();
    }

    /**
     * Test project vote for a regular user
     */
    public function testVote()
    {
        /** @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);
        $user->id = 123;

        $user->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->token->expects($this->any())
        ->method('getUser')
        ->will($this->returnValue($user));

        $vote = $this->voter->vote($this->token, $this->project, ['edit']);
        $this->assertEquals(-1, $vote, 'It correctly votes on the subject and denies editing');
    }

    /**
     * Test project vote for an admin user
     */
    public function testAdminVote()
    {
        /** @var UserInterface|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->createMock(User::class);

        $user->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(true));

        $this->token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $vote = $this->voter->vote($this->token, $this->project, ['edit']);
        $this->assertEquals(1, $vote, 'It correctly votes on the subject and allows editing');
    }
}
