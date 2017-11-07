<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPagePublished;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to publish a given widget page.
 */
class PublishWidgetPageCommandHandler extends WidgetPageCommandHandler
{

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
        parent::__construct($eventBus, $documentManager, $user);
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

        $originalWidgetPage->setLastUpdatedBy($this->user->id);
        $originalWidgetPage->publish();

        $this->determineFacetTargeting($originalWidgetPage);

        $this->documentManager->persist($originalWidgetPage);
        $this->documentManager->flush();

        // Remove the cached version.
        if (file_exists(WWW_ROOT . '/widgets/layout/' . $originalWidgetPage->getId() . '.js')) {
            return unlink(WWW_ROOT . '/widgets/layout/' . $originalWidgetPage->getId() . '.js');
        }

        // Dispatch the event.
        //$this->eventBus->handle(new WidgetPagePublished($originalWidgetPage));
    }
}
