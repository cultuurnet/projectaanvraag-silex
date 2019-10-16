<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker\Event;

class ArticleLinkCreatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the getters and setters.
     */
    public function testGetAndSet()
    {   
        $projectActive = false;
        $articleLinkCreated = new ArticleLinkCreated('the-url', 'the-cdbid', $projectActive);
        $this->assertEquals('the-url', $articleLinkCreated->getUrl());
        $this->assertEquals('the-cdbid', $articleLinkCreated->getCdbid());

        $articleLinkCreated->setUrl('new-url');
        $articleLinkCreated->setCbid('new-cdbid');
        $this->assertEquals('new-url', $articleLinkCreated->getUrl());
        $this->assertEquals('new-cdbid', $articleLinkCreated->getCdbid());
    }
}
