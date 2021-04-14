<?php

namespace CultuurNet\ProjectAanvraag\Curatoren;

use GuzzleHttp\ClientInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests the CuratorenClient class.
 */
class CuratorenClientTest extends TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $guzzleClient = $this->createMock(ClientInterface::class);
        $guzzleClientTwo = clone $guzzleClient;
        $client = new CuratorenClient($guzzleClient);

        $this->assertSame($guzzleClient, $client->getClient());

        $client->setClient($guzzleClientTwo);
        $this->assertSame($guzzleClientTwo, $client->getClient());
    }

    /**
     * Test the search articles method.
     */
    public function testSearchArticles()
    {

        $expectedOptions = [
          'query' => ['about' => 'my-cdbid'],
        ];
        $expectedResult = [
            'cdbid' => 'test',
        ];

        $guzzleClient = $this->createMock(ClientInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $client = new CuratorenClient($guzzleClient);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode($expectedResult));

        $guzzleClient->expects($this->once())
            ->method('request')
            ->with('GET', 'news_articles', $expectedOptions)
            ->willReturn($response);

        $result = $client->searchArticles('my-cdbid');
        $this->assertEquals($expectedResult, $result);
    }
}
