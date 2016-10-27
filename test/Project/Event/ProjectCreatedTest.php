<?php

namespace CultuurNet\ProjectAanvraag\Project\Event;

class ProjectCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the ProjectCreated event
     */
    public function testProjectCreatedEvent()
    {
        $projectCreated = new ProjectCreated(123);
        $this->assertInstanceOf(ProjectCreated::class, $projectCreated);

        $this->assertEquals($projectCreated->getId(), 123, 'The id is correctly returned');

        // Test setters
        $projectCreated->setId(2);

        $this->assertEquals($projectCreated->getId(), 2, 'The id is correctly returned');
    }
}
