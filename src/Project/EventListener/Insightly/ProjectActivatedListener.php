<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\GroupIdConverter;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectActivated;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class ProjectActivatedListener
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
     * @var GroupIdConverter
     */
    private $groupIdConverter;

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
        GroupIdConverter $groupIdConverter,
        bool $useNewInsightlyInstance,
        LoggerInterface $logger
    ) {
        $this->insightlyClient = $insightlyClient;
        $this->entityManager = $entityManager;
        $this->groupIdConverter = $groupIdConverter;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
        $this->logger = $logger;
    }

    public function handle(ProjectActivated $projectActivated): void
    {
        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $projectId = $projectActivated->getProject()->getId();

        /** @var \CultuurNet\ProjectAanvraag\Entity\Project $project */
        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->find($projectId);
        if (!$project) {
            $this->logger->error('Project with id ' . $projectId . ' not found inside internal database');
            return;
        }

        $insightlyOpportunityId = new Id($project->getOpportunityIdInsightly());
        $this->insightlyClient->opportunities()->updateStage($insightlyOpportunityId, OpportunityStage::closed());
        $this->insightlyClient->opportunities()->updateState($insightlyOpportunityId, OpportunityState::won());

        $integrationType = $this->groupIdConverter->toIntegrationType($projectActivated->getProject()->getGroupId());

        $project = (new Project(
            new Name($projectActivated->getProject()->getName()),
            ProjectStage::live(),
            ProjectStatus::completed(),
            new Description($projectActivated->getProject()->getDescription()),
            $integrationType
        ))->withCoupon(new Coupon($projectActivated->getUsedCoupon()));

        // Todo: Link opportunity
        $linkedContactId = $this->insightlyClient->opportunities()->getLinkedContactId($insightlyOpportunityId);
        $this->insightlyClient->projects()->createWithContact(
            $project,
            $linkedContactId
        );
    }
}
