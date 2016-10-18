<?php

namespace CultuurNet\ProjectAanvraag\Insightly\Item;

use CultuurNet\ProjectAanvraag\Insightly\AbstractInsightlyClientTest;
use CultuurNet\ProjectAanvraag\JsonAssertionTrait;

class ProjectTest extends AbstractInsightlyClientTest
{
    use JsonAssertionTrait;

    /**
     * Test Project
     */
    public function testProject()
    {
        $client = $this->getMockClient('getProjects.json');
        $projects = $client->getProjects();

        /** @var Project $project */
        $project = reset($projects);

        $this->assertJsonEquals(json_encode($project), 'Insightly/data/serialized/project.json');

        // Set additional properties
        $project->setOpportunityId(1234);
        $project->setImageUrl('http://www.test.com/test.jpg');
        $project->setDateUpdatedUTC(\DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-18 12:37:25'));
        $project->setPipelineId(123);
        $project->setStageId(1);
        $project->setVisibleTeamId(12);
        $project->setVisibleUserIds([1,2,3]);

        $tag = new Tag();
        $tag->setId('my_tag');

        $project->setTags(new EntityList([$tag]));

        $link = new Link();
        $link->setId(123);

        $project->setLinks(new EntityList([$link, $link]));

        $this->assertEquals($project->getName(), 'Begeleiding ontwikkeling website voor "groote oorlog"', 'It correctly returns the label');
        $this->assertEquals($project->getStatus(), Project::STATUS_ABANDONED, 'It correctly returns the status');
        $this->assertEquals($project->getDetails(), 'ConceptPMTesting & oplevering', 'It correctly returns the details');
        $this->assertEquals($project->getOpportunityId(), 1234, 'It correctly returns the opportunity id');
        $this->assertEquals($project->getStartedDate(), \DateTime::createFromFormat('Y-m-d H:i:s', '2014-01-02 00:00:00'), 'It correctly returns the started date');
        $this->assertEquals($project->getCompletedDate(), null, 'It correctly returns the completed date');
        $this->assertEquals($project->getImageUrl(), 'http://www.test.com/test.jpg', 'It correctly returns the image url');
        $this->assertEquals($project->getResponsibleUserId(), 410743, 'It correctly returns the responsible user id');

        $this->assertEquals($project->getOwnerUserId(), 531507, 'It correctly returns the owner user id');
        $this->assertEquals($project->getDateCreatedUTC(), \DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-16 12:37:25'), 'It correctly returns the date created');
        $this->assertEquals($project->getDateUpdatedUTC(), \DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-18 12:37:25'), 'It correctly returns the date updated');

        $this->assertEquals($project->getCategoryId(), 1256112, 'It correctly returns the category id');
        $this->assertEquals($project->getPipelineId(), 123, 'It correctly returns the pipeline id');
        $this->assertEquals($project->getStageId(), 1, 'It correctly returns the stage id');
        $this->assertEquals($project->getVisibleTo(), Project::VISIBILITY_EVERYONE, 'It correctly returns the visible to');
        $this->assertEquals($project->getVisibleTeamId(), 12, 'It correctly returns the visible team id');
        $this->assertEquals($project->getVisibleUserIds(), [1, 2, 3], 'It correctly returns the visible user ids');
        $this->assertEquals($project->getTags(), new EntityList([$tag]), 'It correctly returns the tags');
        $this->assertEquals($project->getLinks(), new EntityList([$link, $link]), 'It correctly returns the links');
        $this->assertEquals(count($project->getLinks()), 2, 'It correctly returns the number of links');
        $this->assertEquals($project->canDelete(), true, 'It correctly returns the canDelete state');
        $this->assertEquals($project->canEdit(), true, 'It correctly returns the canEdit state');
    }
}
