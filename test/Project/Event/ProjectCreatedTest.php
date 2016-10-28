<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

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

        $projectCreated = new ProjectCreated($project);
        $projectCreated->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $projectCreated->getProject(), 'The project is correctly returned');
    }
}
