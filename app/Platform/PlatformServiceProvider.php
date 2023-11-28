<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Platform;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;

final class PlatformServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple[PlatformClientInterface::class] = function (Application $app) {
            return new PlatformClient(
                $app['config']['platform_host'],
                $app['session'],
                $app['uitid_user_session_service']
            );
        };
    }
}
