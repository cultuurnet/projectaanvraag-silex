<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\UpdateWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class UpdateWidgetPageCommandHandler
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
     * @var UserInterface
     */
    protected $user;

    /**
     * CreateProjectCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentManager $documentManager
     *
     * @param UserInterface $user
     *
     * @internal param DocumentRepository $documentRepository
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, UserInterface $user)
    {
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->user = $user;
    }

    /**
     * Handle the command
     *
     * @param UpdateWidgetPage $updateWidgetPage
     */
    public function handle(UpdateWidgetPage $updateWidgetPage)
    {
        $originalWidgetPage = $updateWidgetPage->getWidgetPage();
        $newWidgetPage = $updateWidgetPage->getNewWidgetPage();

        $widgetPageToSave = null;
        if (!$originalWidgetPage->isDraft()) {
            $widgetPageToSave = $newWidgetPage;
            $widgetPageToSave->setCreatedByUser($originalWidgetPage->getCreatedByUser());
        } else {
            $widgetPageToSave = $originalWidgetPage;
            $widgetPageToSave->setRows($newWidgetPage->getRows());
            $widgetPageToSave->setTitle($newWidgetPage->getTitle());
            $widgetPageToSave->setCss($newWidgetPage->getCss());
        }

        $widgetPageToSave->setLastUpdatedByUser($this->user->id);
        $widgetPageToSave->setAsDraft();

        $this->documentManager->persist($widgetPageToSave);
        $this->documentManager->flush();

        // Dispatch the event.
        $this->eventBus->handle(new WidgetPageUpdated($newWidgetPage));
    }
}
