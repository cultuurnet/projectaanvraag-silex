<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectBlocked;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class ProjectBlockedListener
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var boolean
     */
    private $useNewInsightlyInstance;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        InsightlyClient $insightlyClient,
        EntityManagerInterface $entityManager,
        bool $useNewInsightlyInstance,
        LoggerInterface $logger
    ) {
        $this->insightlyClient = $insightlyClient;
        $this->entityManager = $entityManager;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
        $this->logger = $logger;
    }

    public function handle(ProjectBlocked $projectBlocked): void
    {
        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $projectId = $projectBlocked->getProject()->getId();

        /** @var \CultuurNet\ProjectAanvraag\Entity\Project $project */
        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->find($projectId);
        if (!$project) {
            $this->logger->error('Project with id ' . $projectId . ' not found inside internal database');
            return;
        }

        if ($project->getProjectIdInsightly()) {
            $insightlyProjectId = new Id($project->getProjectIdInsightly());
            $this->insightlyClient->projects()->updateStatus($insightlyProjectId, ProjectStatus::cancelled());
        }

        if ($project->getOpportunityIdInsightly()) {
            $insightlyOpportunityId = new Id($project->getOpportunityIdInsightly());
            $this->insightlyClient->opportunities()->updateState($insightlyOpportunityId, OpportunityState::suspended());
        }
    }
}
