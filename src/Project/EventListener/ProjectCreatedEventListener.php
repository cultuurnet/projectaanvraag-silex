<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Entity\UserInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project as InsightlyProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectEvent;
use Doctrine\ORM\EntityManagerInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;

class ProjectCreatedEventListener extends ProjectCrudEventListener
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * ProjectDeletedEventListener constructor.
     * @param InsightlyClientInterface $insightlyClient
     * @param array $insightlyConfig
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(InsightlyClientInterface $insightlyClient, array $insightlyConfig, EntityManagerInterface $entityManager)
    {
        parent::__construct($insightlyClient, $insightlyConfig);

        $this->entityManager = $entityManager;
    }

    /**
     * Handle the command
     * @param ProjectEvent $projectCreated
     * @throws \Exception
     */
    public function handle(ProjectEvent $projectCreated)
    {
        parent::handle($projectCreated);

        /**
         * @var ProjectInterface $project
         * @var ProjectCreated $projectCreated
         */
        $project = $projectCreated->getProject();

        /** @var UserInterface $user */
        $localUser = $projectCreated->getUser();

        /**
         * 1. Create a new contact when no InsightlyContactId is available
         * or if the contact id does not exist in insightly.
         */
        $createNew = $localUser->getInsightlyContactId() === 0 || $localUser->getInsightlyContactId() === null;
        if (!$createNew) {
            try {
                $this->insightlyClient->getContact($localUser->getInsightlyContactId());
            } catch (ClientErrorResponseException $e) {
                // User id is not found. Create a new contact.
                if ($e->getResponse()->getStatusCode() === 404) {
                    $createNew = true;
                }
            }
        }

        if ($createNew) {
            $insightlyContact = $this->createInsightyContact($localUser);
            $localUser->setInsightylContactId($insightlyContact->getId());
            $this->entityManager->merge($localUser);
            $this->entityManager->flush();
        }

        /**
         * 2. Create Insightly project
         */
        $this->insightlyProject = new InsightlyProject();

        $this->insightlyProject->setName($project->getName());
        $this->insightlyProject->setStatus(Project::STATUS_IN_PROGRESS);
        $this->insightlyProject->setCategoryId($this->insightlyConfig['categories'][$project->getGroupId()]);
        $this->insightlyProject->setDetails($project->getDescription());

        // Link the Insightly user
        $link = new Link();
        $link->setContactId($localUser->getInsightlyContactId());
        $link->setRole('Aanvrager');

        $this->insightlyProject->addLink($link);

        // Custom fields: Test environment
        if (!empty($this->insightlyConfig['custom_fields']['test_key']) && !empty($project->getTestConsumerKey())) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['test_key'], $project->getTestConsumerKey());
        }

        // Custom fields: Live environment
        if (!empty($this->insightlyConfig['custom_fields']['live_key']) && !empty($project->getLiveConsumerKey())) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['live_key'], $project->getLiveConsumerKey());
        }

        // Custom fields: Coupon
        if (!empty($this->insightlyConfig['custom_fields']['coupon']) && !empty($projectCreated->getUsedCoupon())) {
            $this->insightlyProject->addCustomField($this->insightlyConfig['custom_fields']['coupon'], $projectCreated->getUsedCoupon());
        }

        // Create the project
        $this->createInsightlyProject();

        /**
         * 3. Update local db record
         */
        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->find($project->getId());
        $project->setInsightlyProjectId($this->insightlyProject->getId());
        $this->entityManager->merge($project);
        $this->entityManager->flush();

        /**
         * 4. Update the project pipeline and pipeline stage
         */
        if ($projectCreated->getUsedCoupon()) {
            $this->updatePipeline($this->insightlyConfig['pipeline'], $this->insightlyConfig['stages']['live_met_coupon']);
        } else {
            $this->updatePipeline($this->insightlyConfig['pipeline'], $this->insightlyConfig['stages']['test']);
        }
    }

    /**
     * Creates an Insightly contact
     * @param UserInterface $localUser
     * @return Contact
     */
    private function createInsightyContact($localUser)
    {
        /** @var Contact $contact */
        $contact = new Contact();
        $contact->setFirstName($localUser->getFirstName() ?: $localUser->getNick());
        $contact->setLastName($localUser->getLastName() ?: $localUser->getNick());
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, $localUser->getEmail());

        return $this->insightlyClient->createContact($contact);
    }
}
