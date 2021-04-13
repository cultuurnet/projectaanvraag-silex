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

        $this->assertEquals('My project', $createProject->getName(), 'The name is correctly returned');
        $this->assertEquals(123, $createProject->getIntegrationType(), 'The integration type is correctly returned');
        $this->assertEquals('Some description', $createProject->getDescription(), 'The description is correctly returned');

        // Test setters
        $createProject->setDescription('Some new description');
        $createProject->setName('My new project name');
        $createProject->setIntegrationType(12345);

        $this->assertEquals('My new project name', $createProject->getName(), 'The name is correctly returned');
        $this->assertEquals(12345, $createProject->getIntegrationType(), 'The integration type is correctly returned');
        $this->assertEquals('Some new description', $createProject->getDescription(), 'The description is correctly returned');
    }
}
