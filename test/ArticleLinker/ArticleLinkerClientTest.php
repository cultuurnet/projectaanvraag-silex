<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests the ArticleLinkerClient class.
 */
class ArticleLinkerClientTest extends TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $guzzleClient = $this->createMock(ClientInterface::class);
        $guzzleClientTwo = clone $guzzleClient;
        $client = new ArticleLinkerClient($guzzleClient);

        $this->assertSame($guzzleClient, $client->getClient());

        $client->setClient($guzzleClientTwo);
        $this->assertSame($guzzleClientTwo, $client->getClient());
    }

    /**
     * Test the linkArticle method.
     */
    public function testLinkArticle()
    {

        $expectedOptions = [
            'json' =>
                [
                    'url' => 'the-url',
                    'cdbid' => 'the-cdbid',
                ],
        ];

        $guzzleClient = $this->createMock(ClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $client = new ArticleLinkerClient($guzzleClient);

        $guzzleClient->expects($this->once())
            ->method('request')
            ->with('POST', 'linkArticle', $expectedOptions)
            ->willReturn($response);

        $client->linkArticle('the-url', 'the-cdbid');
    }
}
