<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use PHPUnit\Framework\TestCase;

class ProjectDeletedEventListenerTest extends TestCase
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

        $this->eventListener = new ProjectDeletedEventListener($this->insightlyClient, []);
    }

    /**
     * Test the event listener handler
     */
    public function testHandle()
    {
        $insightlyProject = $this->createMock(Project::class);
        $this->insightlyClient
            ->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($insightlyProject));

        $this->insightlyClient
            ->expects($this->any())
            ->method('updateProject')
            ->will($this->returnValue($insightlyProject));

        $project = $this->createMock(ProjectInterface::class);
        $projectDeleted = $this->createMock(ProjectDeleted::class);

        $projectDeleted->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($project));

        $this->eventListener->handle($projectDeleted);

        $this->addToAssertionCount(1);
    }
}
