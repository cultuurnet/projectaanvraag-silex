<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Project\Command\CreateProject;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateProjectCommandHandler
{

    protected $eventBus;

    public function __construct(MessageBusSupportingMiddleware $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    public function handle(CreateProject $createProject)
    {
        $projectCreated = new ProjectCreated(1);
        $this->eventBus->handle($projectCreated);
    }
}
