<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;

class DeleteProjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the DeleteProject command
     */
    public function testDeleteProjectCommand()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->getMock(ProjectInterface::class);
        $createProject = new DeleteProject($project);

        // Test setters
        $createProject->setProject($project);

        $this->assertInstanceOf(ProjectInterface::class, $createProject->getProject(), 'It correctly returns the project');
    }
}
