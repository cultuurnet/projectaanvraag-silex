<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\CommandHandler;

use CultuurNet\ProjectAanvraag\ArticleLinker\Command\CreateArticleLink;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateArticleLinkCommandHandler
{

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * CreateArticleLinkCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * Handle the command
     * @param CreateArticleLink $createArticleLink
     * @throws \Throwable
     */
    public function handle(CreateArticleLink $createArticleLink)
    {
        $articleLinkCreated = new ArticleLinkCreated($createArticleLink->getUrl(), $createArticleLink->getCdbid(), $createArticleLink->getProjectActive());
        $this->eventBus->handle($articleLinkCreated);
    }
}
