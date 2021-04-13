<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\Command;

use PHPUnit\Framework\TestCase;

class CreateArticleLinkTest extends TestCase
{
    /**
     * Test the getters and setters.
     */
    public function testGetAndSet()
    {
        $createArticleLink = new CreateArticleLink('my-url', 'the-cdbid');

        $this->assertEquals('my-url', $createArticleLink->getUrl());
        $this->assertEquals('the-cdbid', $createArticleLink->getCdbid());

        // Test setters
        $createArticleLink->setUrl('my-new-url');
        $createArticleLink->setCdbid('my-new-cdbid');

        $this->assertEquals('my-new-url', $createArticleLink->getUrl());
        $this->assertEquals('my-new-cdbid', $createArticleLink->getCdbid());
    }
}
