<?php

namespace CultuurNet\ProjectAanvraag\Insightly;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

abstract class AbstractInsightlyClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper method for mocking a Client
     *
     * @param null $file
     * @param int $responseCode
     * @return InsightlyClient
     */
    protected function getMockClient($file = null, $responseCode = 200)
    {
        $client = new Client('http://www.test.com/');

        $mock = new MockPlugin();
        $mock->addResponse(new Response($responseCode, null, $file ? $this->getMockData($file) : null));
        $client->addSubscriber($mock);

        return  new InsightlyClient($client, 'api_key');
    }

    /**
     * Helper method for fetching the json data
     *
     * @param $file
     * @return string
     */
    protected function getMockData($file)
    {
        return file_get_contents(__DIR__ . '/data/requests/' . $file);
    }
}
