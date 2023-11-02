<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProjectImportedTest extends TestCase
{
    public function testProjectImportedEvent()
    {
        /** @var ProjectInterface & MockObject $project */
        $project = $this->createMock(ProjectInterface::class);

        /** @var User & MockObject $project */
        $user = $this->createMock(UserInterface::class);

        $projectImported = new ProjectImported($project, $user);

        $this->assertEquals($project, $projectImported->getProject());
        $this->assertEquals($user, $projectImported->getUser());
    }
}
