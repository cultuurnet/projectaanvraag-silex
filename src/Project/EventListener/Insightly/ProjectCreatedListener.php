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
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use Psr\Log\LoggerInterface;

final class ProjectCreatedListener
{
    /**
     * @var IntegrationTypeStorageInterface
     */
    private $integrationTypeStorage;

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
        IntegrationTypeStorageInterface $integrationTypeStorage,
        InsightlyClient $insightlyClient,
        bool $useNewInsightlyInstance,
        LoggerInterface $logger
    ) {
        $this->integrationTypeStorage = $integrationTypeStorage;
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

        $project = $projectCreated->getProject();
        $projectId = $project->getId();
        $groupId = $project->getGroupId();

        if (!$groupId) {
            $this->logger->error('Project created with id ' . $projectId . ' has no group id');
            return;
        }

        // Load the integration type info based on the group id (because $project->getGroup() will return null since it
        // was not serialized when the event was published on the AMQP queue).
        $integrationType = $this->integrationTypeStorage->load($groupId);
        if (!$integrationType) {
            $this->logger->error('Project created with id ' . $projectId . ' has a group id ' . $groupId . ' that has no integration type configured');
            return;
        }

        $insightlyIntegrationType = $integrationType->getInsightlyIntegrationType();
        if (!$insightlyIntegrationType) {
            $this->logger->error('Project created with id ' . $projectId . ' and group id ' . $groupId . ' has no Insightly integration type configured');
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
