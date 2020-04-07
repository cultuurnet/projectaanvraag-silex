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

        $this->assertEquals($createArticleLink->getUrl(), 'my-url');
        $this->assertEquals($createArticleLink->getCdbid(), 'the-cdbid');

        // Test setters
        $createArticleLink->setUrl('my-new-url');
        $createArticleLink->setCdbid('my-new-cdbid');

        $this->assertEquals($createArticleLink->getUrl(), 'my-new-url');
        $this->assertEquals($createArticleLink->getCdbid(), 'my-new-cdbid');
    }
}
