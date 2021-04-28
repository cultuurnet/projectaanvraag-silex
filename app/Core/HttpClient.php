<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Core;

use Guzzle\Http\Client;
use CultureFeed_HttpResponse;
use CultureFeed_DefaultHttpClient;
use Exception;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;

class HttpClient extends CultureFeed_DefaultHttpClient
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function request($url, $httpHeaders = [], $method = 'GET', $postData = '')
    {
        // Remove oauth parameters from query string. They are not used by the
        // CultuurNet webservices and only clutter our debug log.
        //preg_match_all('/(?<=[?|&])oauth_.*?[&$]/', $url, $matches);
        $url = preg_replace('/(?<=[?|&])oauth_.*?[&$]/', '', $url);

        switch ($method) {
            case 'GET':
                $request = $this->client->get($url);
                break;

            case 'POST':
                if (is_array($postData)) {
                    // $postData contains file multipart/form-data, including file data.
                    // Unfortunately the interfaces of Guzzle\Http and CultureFeed_HttpClient are
                    // incompatible here, so we need to fall back to CultureFeed_HttpClient behavior.
                    return parent::request(
                        $url,
                        $httpHeaders,
                        $method,
                        $postData
                    );
                } else {
                    $request = $this->client->post($url, null, $postData);
                }
                break;

            case 'DELETE':
                $request = $this->client->delete($url);
                break;

            default:
                throw new Exception('Unsupported HTTP method ' . $method);
        }

        $request->getQuery()->useUrlEncoding(true);

        foreach ($httpHeaders as $header) {
            list($name, $value) = explode(':', $header, 2);
            $request->addHeader($name, $value);
        }

        // Ensure by default we indicate a Content-Type of
        // application/x-www-form-urlencoded for requests containing a body.
        if ($request instanceof EntityEnclosingRequestInterface &&
            !$request->hasHeader('Content-Type') &&
            empty($request->getPostFiles())) {
            $request->setHeader(
                'Content-Type',
                'application/x-www-form-urlencoded'
            );
        }

        try {
            $response = $request->send();
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }

        $culturefeedResponse = new CultureFeed_HttpResponse(
            $response->getStatusCode(),
            $response->getBody(true)
        );

        return $culturefeedResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function setTimeout($timeout)
    {
        $guzzleConfig = [
            Client::CURL_OPTIONS => [
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => $timeout,
            ],
        ];
        $this->client->setConfig($guzzleConfig);
    }
}
