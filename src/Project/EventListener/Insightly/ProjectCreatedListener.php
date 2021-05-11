<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\FirstName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\LastName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\Project\ProjectServiceInterface;
use Psr\Log\LoggerInterface;

final class ProjectCreatedListener
{
    /**
     * @var ProjectServiceInterface
     */
    private $projectService;

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
        ProjectServiceInterface $projectService,
        InsightlyClient $insightlyClient,
        bool $useNewInsightlyInstance,
        LoggerInterface $logger
    ) {
        $this->projectService = $projectService;
        $this->insightlyClient = $insightlyClient;
        $this->useNewInsightlyInstance = $useNewInsightlyInstance;
        $this->logger = $logger;
    }

    public function handle(ProjectCreated $projectCreated): void
    {
        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $projectId = $projectCreated->getProject()->getId();

        // Re-load the project from the project service, so non-serialized properties like $group get hydrated.
        try {
            $project = $this->projectService->loadProject($projectId);
        } catch (\Exception $e) {
            $this->logger->warning('Created project with id ' . $projectId . ' could not be loaded');
            return;
        }

        $group = $project->getGroup();
        if (!$group) {
            $this->logger->warning('Project with id ' . $projectId . ' created without group');
            return;
        }

        $insightlyIntegrationType = $group->getInsightlyIntegrationType();
        if (!$insightlyIntegrationType) {
            $this->logger->warning('Project with id ' . $projectId . ' created with group id ' . $group->getId() . ' without Insightly integration type');
            // The project has no Insightly integration type configured for its group id in integration_types.yml
            // For example CultureFeed or SAPI2 (not used anymore in reality)
            return;
        }

        $contactId = $this->insightlyClient->contacts()->create(
            $this->createContact($projectCreated->getUser())
        );

        $this->logger->debug('Created contact with id ' . $contactId->getValue());

        if ($projectCreated->getUsedCoupon()) {
            $insightlyProjectId = $this->insightlyClient->projects()->createWithContact(
                $this->createProject($project, $insightlyIntegrationType),
                $contactId
            );
            $this->logger->debug('Created project with id ' . $insightlyProjectId->getValue());
        } else {
            $insightlyOpportunityId =  $this->insightlyClient->opportunities()->createWithContact(
                $this->createOpportunity($project, $insightlyIntegrationType),
                $contactId
            );
            $this->logger->debug('Created opportunity with id ' . $insightlyOpportunityId->getValue());
        }
    }

    private function createContact(UserInterface $user): Contact
    {
        return new Contact(
            new FirstName(empty($user->getFirstName()) ? $user->getNick() : $user->getFirstName()),
            new LastName(empty($user->getLastName()) ? $user->getNick() : $user->getLastName()),
            new Email($user->getEmail())
        );
    }

    private function createOpportunity(ProjectInterface $project, IntegrationType $integrationType): Opportunity
    {
        return new Opportunity(
            new Name($project->getName()),
            OpportunityState::open(),
            OpportunityStage::test(),
            new Description($project->getDescription()),
            $integrationType
        );
    }

    private function createProject(ProjectInterface $project, IntegrationType $integrationType): Project
    {
        return new Project(
            new Name($project->getName()),
            ProjectStage::live(),
            ProjectStatus::completed(),
            new Description($project->getDescription()),
            $integrationType,
            new Coupon($project->getCoupon())
        );
    }
}
