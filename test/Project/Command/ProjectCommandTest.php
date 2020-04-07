<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectCommandTest extends TestCase
{
    /**
     * Test the abstract ProjectCommand
     */
    public function testProjectCommand()
    {
        /** @var ProjectInterface|MockObject $project */
        $project = $this->createMock(ProjectInterface::class);
        $command = $this->getMockForAbstractClass(ProjectCommand::class, [$project]);
        //$createProject = new DeleteProject($project);

        // Test setters
        $command->setProject($project);

        $this->assertEquals($project, $command->getProject(), 'It correctly returns the project');
    }
}
