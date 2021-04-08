<?php

namespace CultuurNet\ProjectAanvraag\Project\Command;

use PHPUnit\Framework\TestCase;

class CreateProjectTest extends TestCase
{
    /**
     * Test the CreateProject command
     */
    public function testCreateProjectCommand()
    {
        $createProject = new CreateProject('My project', 'Some description', 123);

        $this->assertEquals($createProject->getName(), 'My project', 'The name is correctly returned');
        $this->assertEquals($createProject->getIntegrationType(), 123, 'The integration type is correctly returned');
        $this->assertEquals($createProject->getDescription(), 'Some description', 'The description is correctly returned');

        // Test setters
        $createProject->setDescription('Some new description');
        $createProject->setName('My new project name');
        $createProject->setIntegrationType(12345);

        $this->assertEquals($createProject->getName(), 'My new project name', 'The name is correctly returned');
        $this->assertEquals($createProject->getIntegrationType(), 12345, 'The integration type is correctly returned');
        $this->assertEquals($createProject->getDescription(), 'Some new description', 'The description is correctly returned');
    }
}
