<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectBlockedEventListenerTest extends \PHPUnit_Framework_TestCase
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
        $this->insightlyClient = $this
            ->getMockBuilder(InsightlyClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventListener = new ProjectBlockedEventListener($this->insightlyClient);
    }

    /**
     * Test the event listener handler
     */
    public function testHandle()
    {
        $insightlyProject = $this->getMock(Project::class);
        $this->insightlyClient
            ->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($insightlyProject));

        $this->insightlyClient
            ->expects($this->any())
            ->method('updateProject')
            ->will($this->returnValue($insightlyProject));

        $project = $this->getMock(ProjectInterface::class);
        $projectBlocked = $this->getMockBuilder(ProjectBlocked::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectBlocked->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($project));

        $this->eventListener->handle($projectBlocked);
    }
}
