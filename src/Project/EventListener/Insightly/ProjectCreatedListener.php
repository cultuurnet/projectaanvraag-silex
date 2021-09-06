<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\EventListener\Insightly;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\GroupIdConverter;
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
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

final class ProjectCreatedListener
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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

    public function handle(ProjectCreated $projectCreated): void
    {
        $this->logger->debug('Start handling ProjectCreated for ' . $projectCreated->getProject()->getName());

        if (!$this->useNewInsightlyInstance) {
            $this->logger->debug('Not using new Insightly instance');
            return;
        }

        $projectId = $projectCreated->getProject()->getId();

        /** @var \CultuurNet\ProjectAanvraag\Entity\Project $project */
        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->find($projectId);
        if (!$project) {
            $this->logger->error('Project with id ' . $projectId . ' not found inside internal database');
            return;
        }

        $groupId = $project->getGroupId();
        if (!$groupId) {
            $this->logger->error('Project created with id ' . $projectId . ' has no group id');
            return;
        }

        try {
            $insightlyIntegrationType = $this->groupIdConverter->toIntegrationType($groupId);
        } catch (InvalidArgumentException $invalidArgumentException) {
            $this->logger->error('Error when converting groupId: ' . $invalidArgumentException->getMessage());
            return;
        }

        $contact = $this->createContactObject($projectCreated->getUser());
        try {
            $contactId = $this->insightlyClient->contacts()->getByEmail($contact->getEmail())->getId();

            if ($contactId === null) {
                $this->logger->error('The id of contact ' . $contact->getEmail()->getValue() . ' is not set in Insightly.');
                return;
            }
            $this->logger->debug('Found contact with id ' . $contactId->getValue());
        } catch (RecordNotFound $exception) {
            $contactId = $this->insightlyClient->contacts()->create($contact);
            $this->logger->debug('Created contact with id ' . $contactId->getValue());
        }

        if ($projectCreated->getUsedCoupon()) {
            $insightlyProjectId = $this->insightlyClient->projects()->createWithContact(
                $this->createProjectObject($project, $insightlyIntegrationType),
                $contactId
            );

            $project->setProjectIdInsightly($insightlyProjectId->getValue());

            $this->logger->debug('Created project with id ' . $insightlyProjectId->getValue());
        } else {
            $insightlyOpportunityId =  $this->insightlyClient->opportunities()->createWithContact(
                $this->createOpportunityObject($project, $insightlyIntegrationType),
                $contactId
            );

            $project->setOpportunityIdInsightly($insightlyOpportunityId->getValue());

            $this->logger->debug('Created opportunity with id ' . $insightlyOpportunityId->getValue());
        }

        $this->entityManager->flush();

        $this->logger->debug('Finished handling ProjectCreated for ' . $projectCreated->getProject()->getName());
    }

    private function createContactObject(UserInterface $user): Contact
    {
        return new Contact(
            new FirstName(empty($user->getFirstName()) ? $user->getNick() : $user->getFirstName()),
            new LastName(empty($user->getLastName()) ? $user->getNick() : $user->getLastName()),
            new Email($user->getEmail())
        );
    }

    private function createOpportunityObject(ProjectInterface $project, IntegrationType $integrationType): Opportunity
    {
        return new Opportunity(
            new Name($project->getName()),
            OpportunityState::open(),
            OpportunityStage::test(),
            new Description($project->getDescription()),
            $integrationType
        );
    }

    private function createProjectObject(ProjectInterface $project, IntegrationType $integrationType): Project
    {
        $projectObject = new Project(
            new Name($project->getName()),
            ProjectStage::live(),
            ProjectStatus::completed(),
            new Description($project->getDescription()),
            $integrationType
        );

        if (!empty($project->getCoupon())) {
            $projectObject = $projectObject->withCoupon(new Coupon($project->getCoupon()));
        }

        return $projectObject;
    }
}
