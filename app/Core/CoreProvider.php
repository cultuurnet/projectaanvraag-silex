<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use Doctrine\Common\Annotations\AnnotationReader;
use JMS\Serializer\SerializerBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use SimpleBus\JMSSerializerBridge\SerializerMetadata;

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

        $pimple['database.installer'] = function (Container $pimple) {
            return new DatabaseSchemaInstaller($pimple);
        };

        $pimple['culturefeed_oauth_client'] = $pimple->extend(
            'culturefeed_oauth_client',
            function (\CultureFeed_DefaultOAuthClient $OAuthClient, Container $pimple) {
                $OAuthClient->setHttpClient($pimple['culturefeed_http_client']);

                return $OAuthClient;
            }
        );

        /**
         * Culturefeed HTTP Client adapter for a Guzzle HTTP client.
         */
        $pimple['culturefeed_http_client'] = function (Container $pimple) {
            $httpClient = new \CultuurNet\CulturefeedHttpGuzzle\HttpClient(
                $pimple['culturefeed_http_client_guzzle']
            );

            if (isset($pimple['config']['httpclient']) && isset($pimple['config']['httpclient']['timeout'])) {
                $httpClientTimeOut = $app['config']['httpclient']['timeout'];
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
            return new \Guzzle\Http\Client();
        };
    }
}
