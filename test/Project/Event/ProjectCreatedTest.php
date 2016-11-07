<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;

class ProjectCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the ProjectCreated event
     */
    public function testProjectCreatedEvent()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this
            ->getMockBuilder(ProjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var User|\PHPUnit_Framework_MockObject_MockObject $project */
        $user = $this
            ->getMockBuilder(UserInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectCreated = new ProjectCreated($project, $user);
        $projectCreated->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $projectCreated->getProject(), 'The project is correctly returned');
    }
}
