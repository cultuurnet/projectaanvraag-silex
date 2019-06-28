<?php

namespace CultuurNet\ProjectAanvraag\ArticleLinker;

use CultuurNet\ProjectAanvraag\Coupon\Exception\CouponInUseException;
use CultuurNet\ProjectAanvraag\Coupon\Exception\InvalidCouponException;
use CultuurNet\ProjectAanvraag\Curatoren\CuratorenClient;
use CultuurNet\ProjectAanvraag\Entity\Coupon;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Tests the ArticleLinkerClient class.
 */
class ArticleLinkerClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test if the setters and getters work.
     */
    public function testGetAndSet()
    {
        $guzzleClient = $this->getMockBuilder(ClientInterface::class)->getMock();
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

        $guzzleClient = $this->getMockBuilder(ClientInterface::class)->getMock();
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $client = new ArticleLinkerClient($guzzleClient);

        $guzzleClient->expects($this->once())
            ->method('request')
            ->with('POST', 'linkArticle', $expectedOptions)
            ->willReturn($response);

        $client->linkArticle('the-url', 'the-cdbid');
    }
}
