<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

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
        $this->assertEquals(100, count($projects), 'It contains 100 items');
    }

    /**
     * Test adding query filters to calls
     */
    public function testQueryFilters()
    {
        $client = $this->getMockClient('getProjectsFiltered.json');
        $projects = $client->getProjects(['brief' => true, 'top' => 2, 'skip' => 1, 'count_total' => true]);

        $this->assertEquals(2, count($projects), 'It contains 2 items');
    }
}
