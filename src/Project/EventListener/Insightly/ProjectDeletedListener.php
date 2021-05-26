<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use Psr\Log\LoggerInterface;

final class ProjectDeletedListener
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

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
        bool $useNewInsightlyInstance,
        LoggerInterface $logger
    ) {
        $this->insightlyClient = $insightlyClient;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
        $this->logger = $logger;
    }

    public function handle(ProjectDeleted $projectDeleted): void
    {
        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $project = $projectDeleted->getProject();

        if ($project->getProjectIdInsightly()) {
            $insightlyProjectId = new Id($project->getProjectIdInsightly());
            $this->insightlyClient->projects()->updateStatus($insightlyProjectId, ProjectStatus::abandoned());

            $this->logger->debug('Abandoned project ' . $insightlyProjectId->getValue());
        }

        if ($project->getOpportunityIdInsightly()) {
            $insightlyOpportunityId = new Id($project->getOpportunityIdInsightly());
            $this->insightlyClient->opportunities()->updateState($insightlyOpportunityId, OpportunityState::abandoned());

            $this->logger->debug('Abandoned opportunity ' . $insightlyOpportunityId->getValue());
        }
    }
}
