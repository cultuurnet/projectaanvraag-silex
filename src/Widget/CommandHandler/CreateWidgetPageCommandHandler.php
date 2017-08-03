<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageCreated;
use CultuurNet\ProjectAanvraag\Widget\Command\CreateWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Id\UuidGenerator;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateWidgetPageCommandHandler
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var DocumentManager
     */
    protected $documentManager;

    /**
     * @var UitIdUserInterface
     */
    protected $user;

    /**
     * @var UuidGenerator
     */
    protected $uuidGenerator;

    /**
     * CreateProjectCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentRepository $documentRepository
     * @param UitIdUserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, UserInterface $user, UuidGenerator $uuidGenerator)
    {
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->user = $user;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * Handle the command
     *
     * @param CreateWidgetPage $widgetPage
     */
    public function handle(CreateWidgetPage $createWidgetPage)
    {

        $widgetPage = $createWidgetPage->getWidgetPage();
        $widgetPage->setId($this->uuidGenerator->generate($this->documentManager, $widgetPage));
        $widgetPage->setAsDraft();
        $widgetPage->setCreatedByUser($this->user->id);
        $widgetPage->setLastUpdatedByUser($this->user->id);


        // Save the project to the local database.
        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();

        // Dispatch the event.
        $this->eventBus->handle(new WidgetPageCreated($widgetPage));
    }
}
