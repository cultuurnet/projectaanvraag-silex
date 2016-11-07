<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;

class InsighltyClientTest extends AbstractInsightlyClientTest
{
    /**
     * Test client request method
     * @expectedException \Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testRequestExceptionHandling()
    {
        $client = $this->getMockClient(null, 404);
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
        /** @var Project|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this
            ->getMockBuilder(Project::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = $this->getMockClient('getProject.json');
        $project = $client->updateProject($project);

        $this->assertInstanceOf('\CultuurNet\ProjectAanvraag\Insightly\Item\Project', $project, 'It correctly returns an Insightly project');
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
