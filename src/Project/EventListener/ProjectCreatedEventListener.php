<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\User;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use Doctrine\ORM\EntityManagerInterface;

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
     * ProjectDeletedEventListener constructor.
     * @param InsightlyClientInterface $insightlyClient
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(InsightlyClientInterface $insightlyClient, EntityManagerInterface $entityManager)
    {
        $this->insightlyClient = $insightlyClient;
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
