<?php

namespace CultuurNet\ProjectAanvraag\Sentry;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sentry\ErrorHandler;
use function Sentry\init;

final class SentryServiceProvider implements ServiceProviderInterface
{


    public function register(Container $app)
    {
        init(
            [
                'dsn' => $app['config']['sentry']['dsn'],
                'environment' => $app['config']['sentry']['environment'],
            ]
        );
        ErrorHandler::registerOnceErrorHandler();
    }
}
