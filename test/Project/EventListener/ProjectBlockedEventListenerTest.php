<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use PHPUnit\Framework\TestCase;

class ProjectBlockedEventListenerTest extends TestCase
{
    /**
     * @var InsightlyClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyClient;

    /**
     * @var ProjectDeletedEventListener
     */
    protected $eventListener;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->insightlyClient = $this->createMock(InsightlyClientInterface::class);

        $this->eventListener = new ProjectBlockedEventListener($this->insightlyClient, []);
    }

    /**
     * Test the event listener handler
     */
    public function testHandle()
    {
        /** @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject $project */
        $project = $this->createMock(ProjectInterface::class);
        $project->expects($this->any())
            ->method('getInsightlyProjectId')
            ->willReturn(1);

        /** @var Project $insightlyProject */
        $insightlyProject = $this->createMock(Project::class);
        $this->insightlyClient
            ->expects($this->any())
            ->method('getProject')
            ->with(1)
            ->will($this->returnValue($insightlyProject));

        $this->insightlyClient
            ->expects($this->any())
            ->method('updateProject')
            ->with($insightlyProject)
            ->will($this->returnValue($insightlyProject));

        $projectBlocked = $this->createMock(ProjectBlocked::class);

        $projectBlocked->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($project));

        $this->eventListener->handle($projectBlocked);

        $this->addToAssertionCount(1);
    }
}
