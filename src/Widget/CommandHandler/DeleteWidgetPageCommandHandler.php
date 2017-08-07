<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\DeleteWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPageDeleted;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to delete a given widget page.
 */
class DeleteWidgetPageCommandHandler
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
        $this->eventBus = $eventBus;
        $this->documentManager = $documentManager;
        $this->user = $user;
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

        // Dispatch the event.
        $this->eventBus->handle(new WidgetPageDeleted($originalWidgetPage));
    }
}
