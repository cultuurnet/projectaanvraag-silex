<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPagePublished;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Class PublishWidgetPageCommandHandler
 * @package CultuurNet\ProjectAanvraag\Widget\CommandHandler
 */
class PublishWidgetPageCommandHandler
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
     * PublishWidgetPageCommandHandler constructor.
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
     * @param PublishWidgetPage $publishWidgetPage
     */
    public function handle(PublishWidgetPage $publishWidgetPage)
    {

        $originalWidgetPage = $publishWidgetPage->getWidgetPage();


        if (!$originalWidgetPage->isDraft()) {
           // If the widgetPage is already published, we do not have to do anything anymore
            return;
        }

        // Delete the old published widget page(s)
        $this->documentRepository->createQueryBuilder()
            ->remove()
            ->field('id')->equals($originalWidgetPage->getId())
            ->field('draft')->equals(false)
            ->getQuery()
            ->execute();

        $originalWidgetPage->setLastUpdatedByUser($this->user->id);
        $originalWidgetPage->setAsPublished();

        $this->documentManager->persist($originalWidgetPage);
        $this->documentManager->flush();

        // Dispatch the event.
        $this->eventBus->handle(new WidgetPagePublished($originalWidgetPage));
    }
}
