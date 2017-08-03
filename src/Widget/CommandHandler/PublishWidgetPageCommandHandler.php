<?php

namespace CultuurNet\ProjectAanvraag\Widget\CommandHandler;

use CultuurNet\ProjectAanvraag\Widget\Event\WidgetPagePublished;
use CultuurNet\ProjectAanvraag\Widget\WidgetPageInterface;
use CultuurNet\ProjectAanvraag\Widget\Command\PublishWidgetPage;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class PublishWidgetPageCommandHandler
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
   * CreateProjectCommandHandler constructor.
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
   *
   * @param PublishWidgetPage $widgetPage
   */
  public function handle(PublishWidgetPage $widgetPage)
  {
    //@TODO implement publishing of the widget page
   // die('handling publishing command');

    // Dispatch the event.
    $this->eventBus->handle(new WidgetPagePublished(widgetPage));
  }
}
