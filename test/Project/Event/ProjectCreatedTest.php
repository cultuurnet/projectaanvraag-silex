<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectCreatedTest extends TestCase
{
    /**
     * Test the ProjectCreated event
     */
    public function testProjectCreatedEvent()
    {
        /** @var ProjectInterface & MockObject $project */
        $project = $this->createMock(ProjectInterface::class);

        /** @var User & MockObject $project */
        $user = $this->createMock(UserInterface::class);

        $projectCreated = new ProjectCreated($project, $user);
        $projectCreated->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $projectCreated->getProject(), 'The project is correctly returned');
    }
}
