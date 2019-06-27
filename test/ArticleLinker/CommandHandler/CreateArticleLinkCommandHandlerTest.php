<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\CommandHandler;

use CultuurNet\ProjectAanvraag\ArticleLinker\Command\CreateArticleLink;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

/**
 * Tests the CreateArticleLinkCommandHandler class.
 */
class CreateArticleLinkCommandHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MessageBusSupportingMiddleware|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventBus;

    /**
     * @var CreateArticleLinkCommandHandler
     */
    protected $commandHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventBus = $this
            ->getMockBuilder('SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware')
            ->disableOriginalConstructor()
            ->getMock();

        $this->commandHandler = new CreateArticleLinkCommandHandler($this->eventBus);
    }

    /**
     * Test the handling of a create article link.
     */
    public function testHandleCreateArticleLink()
    {
        $createArticleLink = new CreateArticleLink('my-url', 'my-cdbid');
        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid');

        $this->eventBus->expects($this->once())
            ->method('handle')
            ->with($articleLinkCreated);

        $this->commandHandler->handle($createArticleLink);
    }
}
