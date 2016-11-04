<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class RequestActivationCommandHandler
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var InsightlyClientInterface
     */
    protected $insightlyClient;

    /**
     * @var array
     */
    protected $insightlyConfig;

    /**
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeedLive
     * @param User $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, User $user, InsightlyClientInterface $insightlyClient, $insightlyConfig)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->user = $user;
        $this->insightlyClient = $insightlyClient;
        $this->insightlyConfig = $insightlyConfig;
    }

    /**
     * Handle the command
     * @param RequestActivation $requestActivation
     * @throws \Throwable
     */
    public function handle(RequestActivation $requestActivation)
    {
        $project = $requestActivation->getProject();
        $insightlyProject = $this->insightlyClient->getProject($project->getInsightlyProjectId());

        // Update the pipeline stage.
        $this->insightlyClient->updateProjectPipelineStage(
            $project->getInsightlyProjectId(),
            $this->insightlyConfig['pipeline'],
            $this->insightlyConfig['stages']['aanvraag']
        );

        // Create an organisation. (We can't search on VAT, so always create a new)
        $organisation = new Organisation();
        $organisation->setName($requestActivation->getName());
        // @todo The field is currently not known for test, ask Reinout for real field name.
        if ($requestActivation->getVatNumber() && !empty($this->insightlyConfig['custom_fields']['vat']))
        {
            $organisation->addCustomField($this->insightlyConfig['custom_fields']['vat'], $requestActivation->getVatNumber());
        }

        // Add contact.
        $contact = new ContactInfo();
        $contact->setType(ContactInfo::TYPE_EMAIL);
        $contact->setDetail($requestActivation->getEmail());
        $organisation->getContactInfo()->append($contact);

        // Address.
        $givenAddress = $requestActivation->getAddress();
        $address = new Address();
        $address->setType('WORK');
        $address->setStreet($givenAddress->getStreet() . ' ' . $givenAddress->getNumber());
        $address->setCity($givenAddress->getCity());
        $address->setPostal($givenAddress->getPostal());
        $organisation->getAddresses()->append($address);

        // Save organisation
        $organisation = $this->insightlyClient->createOrganisation($organisation);

        // Save the link organisation > project.
        $links = $organisation->getLinks();
        if (isset($links[0])) {
            $link = $links[0];
        }
        else {
            $link = new Link();
        }

        $link->setOrganisationId($organisation->getId());
        $links[0] = $link;
        $insightlyProject->setLinks($links);

        $this->insightlyClient->updateProject($insightlyProject);

        // Update the project state in local db.
        $project->setStatus(ProjectInterface::PROJECT_STATUS_WAITING_FOR_PAYMENT);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

    }
}
