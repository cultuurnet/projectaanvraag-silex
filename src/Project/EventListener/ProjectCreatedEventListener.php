<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use Doctrine\ORM\EntityManagerInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project as InsightlyProject;

class ProjectCreatedEventListener
{
    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $insightlyConfig;

    /**
     * ProjectDeletedEventListener constructor.
     * @param InsightlyClientInterface $insightlyClient
     * @param array $insightlyConfig
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(InsightlyClientInterface $insightlyClient, array $insightlyConfig, EntityManagerInterface $entityManager)
    {
        $this->insightlyClient = $insightlyClient;
        $this->insightlyConfig = $insightlyConfig;
        $this->entityManager = $entityManager;
    }

    /**
     * Handle the command
     * @param ProjectCreated $projectCreated
     * @throws \Exception
     */
    public function handle($projectCreated)
    {
        /** @var ProjectInterface $project */
        $project = $projectCreated->getProject();

        /** @var UserInterface $user */
        $localUser = $projectCreated->getUser();

        // 1. Create a new contact when no InsightlyContactId is available
        if (empty($localUser->getInsightlyContactId())) {
            $localUser->setInsightylContactId($this->createInsightyConctact($localUser)->getId());

            $this->entityManager->merge($localUser);
            $this->entityManager->flush();
        }

        // 2. Create Insightly project
        $insightlyProject = new InsightlyProject();
        $insightlyProject->setName($project->getName());
        $insightlyProject->setStatus(Project::STATUS_IN_PROGRESS);
        $insightlyProject->setCategoryId($this->insightlyConfig['categories'][$project->getGroupId()]);

        // Todo: Add custom field and link field
        $test = $this->insightlyClient->createProject($insightlyProject);
    }

    /**
     * Creates an Insightly contact
     * @param UserInterface $localUser
     * @return Contact
     */
    private function createInsightyConctact($localUser)
    {
        /** @var Contact $contact */
        $contact = new Contact();
        $contact->setFirstName($localUser->getFirstName() ?: $localUser->getNick());
        $contact->setLastName($localUser->getLastName() ?: $localUser->getNick());
        $contact->addContactInfo(ContactInfo::CONTACT_INFO_TYPE_EMAIL, '', '', $localUser->getEmail());

        return $this->insightlyClient->createContact($contact);
    }
}
