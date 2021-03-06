<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\DeleteWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to delete a given widget page.
 */
class DeleteWidgetPageCommandHandler extends WidgetPageCommandHandler
{
    /**
     * @var DocumentRepository
     */
    protected $documentRepository;

    /**
     * DeleteWidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentManager $documentManager
     * @param DocumentRepository $documentRepository
     * @param UserInterface $user
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentManager $documentManager, DocumentRepository $documentRepository, UserInterface $user)
    {
        parent::__construct($eventBus, $documentManager, $user);
        $this->documentRepository = $documentRepository;
    }

    /**
     * Handle the command
     *
     * @param DeleteWidgetPage $deleteWidgetPage
     */
    public function handle(DeleteWidgetPage $deleteWidgetPage)
    {
        $originalWidgetPage = $deleteWidgetPage->getWidgetPage();

        // Delete the old published widget page(s)
        $this->documentRepository->createQueryBuilder()
            ->remove()
            ->field('id')->equals($originalWidgetPage->getId())
            ->getQuery()
            ->execute();

        $this->documentManager->flush();

        // Remove the cached version.
        if (file_exists(WWW_ROOT . '/widgets/layout/' . $originalWidgetPage->getId() . '.js')) {
            return unlink(WWW_ROOT . '/widgets/layout/' . $originalWidgetPage->getId() . '.js');
        }
        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPageDeleted($originalWidgetPage));
    }
}
