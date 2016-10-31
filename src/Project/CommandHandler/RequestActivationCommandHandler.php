<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

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
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     * @param EntityManagerInterface $entityManager
     * @param \CultureFeed $cultureFeedLive
     * @param User $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, EntityManagerInterface $entityManager, User $user)
    {
        $this->eventBus = $eventBus;
        $this->entityManager = $entityManager;
        $this->user = $user;
    }

    /**
     * Handle the command
     * @param RequestActivation $requestActivation
     * @throws \Throwable
     */
    public function handle(RequestActivation $requestActivation)
    {

    }
}
