<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\GroupIdConverter;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\InsightlyClient;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Address;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\TaxNumber;
use CultuurNet\ProjectAanvraag\Project\Event\RequestedActivation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

final class RequestedActivationListener
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

    public function handle(RequestedActivation $requestedActivation): void
    {
        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $projectId = $requestedActivation->getProject()->getId();

        /** @var \CultuurNet\ProjectAanvraag\Entity\Project $project */
        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->find($projectId);
        if (!$project) {
            $this->logger->error('Project with id ' . $projectId . ' not found inside internal database');
            return;
        }

        $insightlyOpportunityId = new Id($project->getOpportunityIdInsightly());
        $this->insightlyClient->opportunities()->updateStage($insightlyOpportunityId, OpportunityStage::request());
        $this->insightlyClient->opportunities()->updateState($insightlyOpportunityId, OpportunityState::won());

        try {
            $organization = $this->getExistingOrganization($requestedActivation);
        } catch (RecordNotFound $recordNotFound) {
            $this->logger->debug('No existing organization found, creating a new one.');

            $organization = $this->createOrganizationObject($requestedActivation);
            $organizationId = $this->insightlyClient->organizations()->create($organization);
            $organization = $organization->withId($organizationId);
        }

        $integrationType = $this->groupIdConverter->toIntegrationType($requestedActivation->getProject()->getGroupId());

        $project = new Project(
            new Name($requestedActivation->getProject()->getName()),
            ProjectStage::live(),
            ProjectStatus::completed(),
            new Description($requestedActivation->getProject()->getDescription()),
            $integrationType
        );

        $projectId = $this->insightlyClient->projects()->createWithContact(
            $project,
            $this->insightlyClient->opportunities()->getLinkedContactId($insightlyOpportunityId)
        );
        $this->insightlyClient->projects()->linkOrganization($projectId, $organization->getId());
        $this->insightlyClient->projects()->linkOpportunity($projectId, $insightlyOpportunityId);

        $project->setProjectIdInsightly($projectId->getValue());
        $this->entityManager->flush();
    }

    private function getExistingOrganization(RequestedActivation $requestedActivation): Organization
    {
        if ($requestedActivation->getVatNumber()) {
            $organization = $this->insightlyClient->organizations()->getByTaxNumber(
                new TaxNumber($requestedActivation->getVatNumber())
            );

            $this->logger->debug('Found organization with VAT ' . $requestedActivation->getVatNumber());

            return $organization;
        }

        $organization = $this->insightlyClient->organizations()->getByEmail(
            new Email($requestedActivation->getEmail())
        );

        $this->logger->debug('Found organization with email ' . $requestedActivation->getEmail());

        return $organization;
    }

    private function createOrganizationObject(RequestedActivation $requestedActivation): Organization
    {
        $organization = new Organization(
            new Name($requestedActivation->getName()),
            new Address(
                $requestedActivation->getAddress()->getStreet(),
                (string) $requestedActivation->getAddress()->getPostal(),
                $requestedActivation->getAddress()->getCity()
            ),
            new Email($requestedActivation->getEmail())
        );

        if ($requestedActivation->getVatNumber()) {
            $organization = $organization->withTaxNumber(new TaxNumber($requestedActivation->getVatNumber()));
        }

        return $organization;
    }
}
