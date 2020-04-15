<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use Guzzle\Http\Exception\ClientErrorResponseException;
use PHPUnit\Framework\MockObject\MockObject;

class InsighltyClientTest extends AbstractInsightlyClientTest
{
    public function testRequestExceptionHandling()
    {
        $client = $this->getMockClient(null, 404);
        $this->expectException(ClientErrorResponseException::class);
        $client->getProjects();
    }

    /**
     * Test requesting of projects
     */
    public function testGetProjects()
    {
        $client = $this->getMockClient('getProjects.json');
        $projects = $client->getProjects();

        $this->assertContainsOnlyInstancesOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Project', $projects, 'It only contains instances of Project');
        $this->assertEquals(3, count($projects), 'It contains 3 items');
    }

    /**
     * Test requesting of a single project
     */
    public function testGetProject()
    {
        $client = $this->getMockClient('getProject.json');
        $project = $client->getProject(1373034);

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Project', $project, 'It correctly returns an Insightly project');
    }

    /**
     * Test updating an Insightly project
     */
    public function testUpdateProject()
    {
        /** @var Project|MockObject $project */
        $project = $this
            ->getMockBuilder(Project::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockClient('getProject.json');
        $project = $client->updateProject($project);

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Project', $project, 'It correctly returns an Insightly project');
    }

    /**
     * Test requesting a contact.
     */
    public function testGetContact()
    {
        $client = $this->getMockClient('getContact.json');
        $contact = $client->getContact(1373034);

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Contact', $contact, 'It correctly returns an Insightly contact');
        $this->assertEquals('my-id', $contact->getId());
        $this->assertEquals('my-first-name', $contact->getFirstName());
        $this->assertEquals('my-last-name', $contact->getLastName());
    }

    /**
     * Test requesting of pipelines
     */
    public function testGetPipelines()
    {
        $client = $this->getMockClient('getPipelines.json');
        $pipelines = $client->getPipelines();

        $this->assertContainsOnlyInstancesOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Pipeline', $pipelines, 'It only contains instances of Pipeline');
        $this->assertEquals(2, count($pipelines), 'It contains 2 items');
    }

    /**
     * Test updates of project pipeline stages.
     */
    public function testUpdateProjectPipelineStage()
    {
        $client = $this->getMockClient('getProject.json');
        $project = $client->updateProjectPipelineStage(1, 'pipelineId', 'stageId');

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Project', $project, 'It correctly returns an Insightly project');
    }

    /**
     * Test adding query filters to calls
     */
    /*public function testQueryFilters()
    {
        $client = $this->getMockClient('getProjects.json');
        $projects = $client->getProjects(['brief' => true, 'top' => 2, 'skip' => 1, 'count_total' => true]);

        $this->assertEquals(3, count($projects), 'It contains 3 items');
    }*/

    /**
     * Test the return of requested organisation
     */
    public function testGetOrganisation()
    {
        $client = $this->getMockClient('getOrganisation.json');
        $organisation = $client->getOrganisation(1);

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Organisation', $organisation, 'It returns an organisation');
        $this->assertEquals("contact-info-id", $organisation->getContactInfo()[0]->getId());
    }

    /**
     * Test the return of organisation when created.
     */
    public function testCreateOrganisation()
    {
        $client = $this->getMockClient('getOrganisation.json');
        $organisation = $client->createOrganisation(new Organisation());

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Organisation', $organisation, 'It returns an organisation');
    }
}
