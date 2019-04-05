<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\CommandHandler;

use CultuurNet\ProjectAanvraag\ArticleLinker\Command\CreateArticleLink;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class CreateArticleLinkCommandHandler
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $cdbid;

    /**
     * @var MessageBusSupportingMiddleware
     */
    protected $eventBus;

    /**
     * CreateProjectCommandHandler constructor.
     * @param MessageBusSupportingMiddleware $eventBus
     */
    public function __construct(MessageBusSupportingMiddleware $eventBus)
    {
        $this->eventBus = $eventBus;
        /*$this->url = $url;
        $this->cdbid = $cdbid;*/
    }

    /**
     * Handle the command
     * @param CreateArticleLink $createArticleLink
     * @throws \Throwable
     */
    public function handle(CreateArticleLink $createArticleLink)
    {
        $articleLinkCreated = new ArticleLinkCreated($createArticleLink->getUrl(), $createArticleLink->getCdbid());
        $this->eventBus->handle($articleLinkCreated);
        // hier kom ik
    }
}
