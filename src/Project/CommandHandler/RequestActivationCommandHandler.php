<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\Project\Event\RequestedActivation;
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

    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
    }

    public function handle(RequestActivation $requestActivation): void
    {
        $project = $requestActivation->getProject();

        $project->setStatus(ProjectInterface::PROJECT_STATUS_WAITING_FOR_PAYMENT);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $this->eventBus->handle(
            new RequestedActivation(
                $project,
                $requestActivation->getPayment(),
                $requestActivation->getName(),
                $requestActivation->getAddress(),
                $requestActivation->getVatNumber()
            )
        );
    }
}
