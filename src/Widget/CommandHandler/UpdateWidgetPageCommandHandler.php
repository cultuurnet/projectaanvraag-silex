<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\UpdateWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to update a given widget page.
 */
class UpdateWidgetPageCommandHandler extends WidgetPageCommandHandler
{

    /**
     * UpdateWidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentManager $documentManager
     * @param UserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, UserInterface $user)
    {
        parent::__construct($eventBus, $documentManager, $user);
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
            $widgetPageToSave->setCreatedBy($originalWidgetPage->getCreatedBy());
        } else {
            $widgetPageToSave = $originalWidgetPage;
            $widgetPageToSave->setRows($newWidgetPage->getRows());
            $widgetPageToSave->setTitle($newWidgetPage->getTitle());
            $widgetPageToSave->setCss($newWidgetPage->getCss());
            $widgetPageToSave->setSelectedTheme($newWidgetPage->getSelectedTheme());
        }
        $widgetPageToSave->setJquery($newWidgetPage->getJquery());
        $widgetPageToSave->setMobile($newWidgetPage->getMobile());

        $widgetPageToSave->setLanguage($newWidgetPage->getLanguage());

        $widgetPageToSave->setLastUpdatedBy($this->user->id);
        $widgetPageToSave->setLastUpdated($_SERVER['REQUEST_TIME']);
        $widgetPageToSave->setAsDraft();

        $this->documentManager->persist($widgetPageToSave);
        $this->documentManager->flush();

        // Remove the cached version.
        if (file_exists(WWW_ROOT . '/widgets/layout/' . $widgetPageToSave->getId() . '.js')) {
            return unlink(WWW_ROOT . '/widgets/layout/' . $widgetPageToSave->getId() . '.js');
        }

        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPageUpdated($newWidgetPage, $originalWidgetPage));
    }
}
