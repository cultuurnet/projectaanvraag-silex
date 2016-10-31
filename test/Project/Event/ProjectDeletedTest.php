<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

class ProjectDeletedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the ProjectDeleted event
     */
    public function testProjectDeletedEvent()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this
            ->getMockBuilder(ProjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectDeleted = new ProjectDeleted($project);
        $projectDeleted->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $projectDeleted->getProject(), 'The project is correctly returned');
    }
}
