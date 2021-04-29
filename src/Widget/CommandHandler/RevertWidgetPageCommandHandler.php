<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Command\RevertWidgetPage;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to revert a given widget page.
 */
class RevertWidgetPageCommandHandler
{
    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * @var DocumentRepository
     */
    protected $documentRepository;

    /**
     * UpgradeWidgetPageCommandHandler constructor.
     *
     * @param MessageBusSupportingMiddleware $eventBus
     * @param DocumentRepository $documentRepository
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus, DocumentRepository $documentRepository)
    {
        $this->eventBus = $eventBus;
        $this->documentRepository = $documentRepository;
    }

    /**
     * Handle the command
     * @param RevertWidgetPage $revertWidgetPage
     */
    public function handle(RevertWidgetPage $revertWidgetPage)
    {

        $widgetPage = $revertWidgetPage->getWidgetPage();

        if (!$widgetPage->isDraft()) {
            // If the widgetPage is already published, we do not have to do anything anymore
            return;
        }

        // Delete all related draft widget pages
        $this->documentRepository->createQueryBuilder()
            ->remove()
            ->field('id')->equals($widgetPage->getId())
            ->field('draft')->equals(true)
            ->getQuery()
            ->execute();
    }
}
