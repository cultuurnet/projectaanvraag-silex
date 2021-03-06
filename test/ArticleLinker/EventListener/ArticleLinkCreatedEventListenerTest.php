<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\EventListener;

use CultuurNet\ProjectAanvraag\ArticleLinker\ArticleLinkerClientInterface;
use CultuurNet\ProjectAanvraag\ArticleLinker\Event\ArticleLinkCreated;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\DoctrineCache;

class ArticleLinkCreatedEventListenerTest extends TestCase
{

    /**
     * Test the event listener handler with an existing / expired cache entity.
     */
    public function testExistingHandle()
    {

        $articleLinkerClient = $this->createMock(ArticleLinkerClientInterface::class);
        $articleLinkerClientTest = $this->createMock(ArticleLinkerClientInterface::class);
        $cacheBackend = $this->createMock(DoctrineCache::class);

        $eventListener = new ArticleLinkCreatedEventListener(
            $articleLinkerClient,
            $articleLinkerClientTest,
            $cacheBackend
        );

        $cacheBackend->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid', false);
        $eventListener->handle($articleLinkCreated);
    }

    /**
     * Test the handler when a cache entity was not found.
     */
    public function testNonExistingHandle()
    {
        $articleLinkerClient = $this->createMock(ArticleLinkerClientInterface::class);
        $articleLinkerClientTest = $this->createMock(ArticleLinkerClientInterface::class);
        $cacheBackend = $this->createMock(DoctrineCache::class);
        $eventListener = new ArticleLinkCreatedEventListener(
            $articleLinkerClient,
            $articleLinkerClientTest,
            $cacheBackend
        );

        $cacheBackend->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $articleLinkerClient->expects($this->once())
            ->method('linkArticle')
            ->with('my-url', 'my-cdbid');

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid', true);
        $eventListener->handle($articleLinkCreated);
    }

    /**
     * Test the event listener handler without cache.
     */
    public function testHandleNoCache()
    {
        $articleLinkerClient = $this->createMock(ArticleLinkerClientInterface::class);
        
        $articleLinkerClientTest = $this->createMock(ArticleLinkerClientInterface::class);

        $eventListener = new ArticleLinkCreatedEventListener(
            $articleLinkerClient,
            $articleLinkerClientTest
        );

        $articleLinkerClientTest->expects($this->once())
            ->method('linkArticle')
            ->with('my-url', 'my-cdbid');

        $articleLinkCreated = new ArticleLinkCreated('my-url', 'my-cdbid', false);
        $eventListener->handle($articleLinkCreated);
    }
}
