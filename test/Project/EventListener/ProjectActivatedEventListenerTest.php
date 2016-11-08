<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;

class ProjectActivatedEventListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InsightlyClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyClient;

    /**
     * @var ProjectActivatedEventListener
     */
    protected $eventListener;

    /**
     * @var ProjectInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

    /**
     * @var Project|PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyProject;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->insightlyClient = $this
            ->getMockBuilder(InsightlyClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventListener = new ProjectActivatedEventListener(
            $this->insightlyClient,
            [
                'pipeline' => 1,
                'custom_fields' => [
                    'live_key' => 'live',
                    'used_coupon' => 'used',
                ],
                'stages' => [
                    'live_met_coupon' => 'coupon',
                    'live_met_abonnement' => 'no coupon',
                ],
            ]
        );

        // Mock the project + all called methods.
        $this->project = $this->getMock(ProjectInterface::class);
        $this->project->expects($this->any())
            ->method('getInsightlyProjectId')
            ->willReturn(1);
        $this->project->expects($this->any())
            ->method('getLiveConsumerKey')
            ->willReturn('liveKey');

        /** @var Project $insightlyProject */
        $this->insightlyProject = $this->getMock(Project::class);
        $this->insightlyProject->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->insightlyClient
            ->expects($this->any())
            ->method('getProject')
            ->with(1)
            ->willReturn($this->insightlyProject);

        $this->eventListener->setInsightlyProject($this->insightlyProject);
    }

    /**
     * Test the event listener handler
     */
    public function testHandle()
    {
        $this->insightlyProject->expects($this->once())
            ->method('addCustomField')
            ->with('live', 'liveKey');

        $this->insightlyClient
            ->expects($this->once())
            ->method('updateProject')
            ->with($this->insightlyProject)
            ->willReturn($this->insightlyProject);

        $this->insightlyClient->expects(($this->once()))
            ->method('updateProjectPipelineStage')
            ->with(1, 'no coupon');

        $projectActivated = new ProjectActivated($this->project);
        $this->eventListener->handle($projectActivated);
    }

    /**
     * Test the handler when a coupon was given.
     */
    public function testHandleWithCoupon()
    {
        $this->insightlyProject->expects($this->at(1))
            ->method('addCustomField')
            ->with('used', 'coupon');

        $this->insightlyClient
            ->expects($this->once())
            ->method('updateProject')
            ->with($this->insightlyProject)
            ->willReturn($this->insightlyProject);

        $this->insightlyClient->expects(($this->once()))
            ->method('updateProjectPipelineStage')
            ->with(1, 'coupon');

        $projectActivated = new ProjectActivated($this->project, 'coupon');
        $this->eventListener->handle($projectActivated);
    }
}
