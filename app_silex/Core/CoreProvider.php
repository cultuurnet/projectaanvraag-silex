<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\CulturefeedHttpGuzzle\HttpClient;
use Doctrine\ODM\MongoDB\Id\UuidGenerator;
use Guzzle\Http\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreProvider implements ServiceProviderInterface
{
    /**
     * @inheritDoc
     */
    public function register(Container $pimple)
    {
        $pimple['service_loader'] = $pimple->protect(
            function ($serviceId) use ($pimple) {
                return $pimple[$serviceId];
            }
        );

        $pimple['culturefeed_oauth_client'] = $pimple->extend(
            'culturefeed_oauth_client',
            function (\CultureFeed_DefaultOAuthClient $OAuthClient, Container $pimple) {
                $OAuthClient->setHttpClient($pimple['culturefeed_http_client']);

                return $OAuthClient;
            }
        );

        /**
         * Culturefeed HTTP Client adapter for a Guzzle HTTP client.
         * @param Container $pimple
         * @return HttpClient
         */
        $pimple['culturefeed_http_client'] = function (Container $pimple) {
            $httpClient = new HttpClient(
                $pimple['culturefeed_http_client_guzzle']
            );

            if (isset($pimple['config']['httpclient']) && isset($pimple['config']['httpclient']['timeout'])) {
                $httpClientTimeOut = $pimple['config']['httpclient']['timeout'];
            } else {
                $httpClientTimeOut = 30;
            }
            $httpClient->setTimeout($httpClientTimeOut);

            return $httpClient;
        };

        /**
         * Guzzle HTTP client.
         */
        $pimple['culturefeed_http_client_guzzle'] = function () {
            return new Client();
        };

        $pimple['uuid_generator'] = function () {
            return new UuidGenerator();
        };
    }
}
