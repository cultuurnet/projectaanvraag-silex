<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageCreated;
use CultuurNet\ProjectAanvraag\Widget\Command\CreateWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Id\UuidGenerator;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to create a new widget page.
 */
class CreateWidgetPageCommandHandler extends WidgetPageCommandHandler
{

    /**
     * @var UuidGenerator
     */
    protected $uuidGenerator;

    /**
     * CreateProjectCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentRepository $documentRepository
     * @param UserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, UserInterface $user, UuidGenerator $uuidGenerator)
    {
        parent::__construct($eventBus, $documentManager, $user);
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
        $widgetPage->setVersion(WidgetPageInterface::CURRENT_VERSION);
        $widgetPage->setAsDraft();
        $widgetPage->setCreatedBy($this->user->id);
        $widgetPage->setLastUpdatedBy($this->user->id);
        $widgetPage->setCreated($_SERVER['REQUEST_TIME']);
        $widgetPage->setLastUpdated($_SERVER['REQUEST_TIME']);

        $widgetPage = $this->determineFacetTargeting($widgetPage);

        // Save the project to the local database.
        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();

        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPageCreated($widgetPage));
    }
}
