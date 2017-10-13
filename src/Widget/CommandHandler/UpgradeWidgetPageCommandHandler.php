<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\User\UserInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\UpgradeWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPagePublished;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Provides a command handler to upgrade a given widget page.
 */
class UpgradeWidgetPageCommandHandler
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
     * UpgradeWidgetPageCommandHandler constructor.
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
     * @param UpgradeWidgetPage $upgradeWidgetPage
     */
    public function handle(UpgradeWidgetPage $upgradeWidgetPage)
    {

        $widgetPage = $upgradeWidgetPage->getWidgetPage();


        if (!$widgetPage->getVersion() === WidgetPageInterface::CURRENT_VERSION) {
           // If the widgetPage is already latest version, we do not have to do anything anymore
            return;
        }

        // Delete all related widget pages (draft and published)
        $this->documentRepository->createQueryBuilder()
            ->remove()
            ->field('id')->equals($widgetPage->getId())
            ->getQuery()
            ->execute();

        $widgetPage->setLastUpdatedBy($this->user->id);
        $widgetPage->setVersion(WidgetPageInterface::CURRENT_VERSION);
        $widgetPage->publish();

        $this->documentManager->persist($widgetPage);
        $this->documentManager->flush();
    }
}
