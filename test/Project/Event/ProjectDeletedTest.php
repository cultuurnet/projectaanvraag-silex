<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectDeletedTest extends TestCase
{
    /**
     * Test the ProjectDeleted event
     */
    public function testProjectDeletedEvent()
    {
        /** @var ProjectInterface & MockObject $project */
        $project = $this->createMock(ProjectInterface::class);

        $projectDeleted = new ProjectDeleted($project);
        $projectDeleted->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $projectDeleted->getProject(), 'The project is correctly returned');
    }
}
