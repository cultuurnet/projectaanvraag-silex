<?php

namespace CultuurNet\ProjectAanvraag\Voter;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Platform\PlatformClientInterface;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProjectVoterTest extends TestCase
{
    /**
     * @var TokenInterface & MockObject
     */
    protected $token;

    /**
     * @var ProjectInterface & MockObject
     */
    protected $project;

    /**
     * @var PlatformClientInterface & MockObject
     */
    private $platformClient;

    /**
     * @var ProjectVoter
     */
    protected $voter;

    public function setUp()
    {
        $this->token = $this->createMock(TokenInterface::class);

        $this->project = $this->createMock(ProjectInterface::class);

        $this->platformClient = $this->createMock(PlatformClientInterface::class);

        $this->voter = new ProjectVoter($this->platformClient, true);
    }

    /**
     * Test project vote for a regular user
     */
    public function testVote()
    {
        /** @var UserInterface & MockObject $user */
        $user = $this->createMock(User::class);
        $user->id = 123;

        $user->expects($this->any())
            ->method('hasRole')
            ->will($this->returnValue(false));

        $this->token->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($user));

        $this->project->expects($this->exactly(2))
            ->method('getPlatformUuid')
            ->will($this->returnValue('platform_uuid'));

        $this->platformClient->expects($this->once())
            ->method('hasAccessOnIntegration')
            ->with('platform_uuid')
            ->willReturn(false);

        $vote = $this->voter->vote($this->token, $this->project, ['edit']);
        $this->assertEquals(-1, $vote, 'It correctly votes on the subject and denies editing');
    }

    /**
     * Test project vote for an admin user
     */
    public function testAdminVote()
    {
        /** @var UserInterface & MockObject $user */
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
