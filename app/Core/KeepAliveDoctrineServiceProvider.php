<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Core\Doctrine\ConnectionKeepAlive;
use Pimple\Container;
use Doctrine\DBAL\DriverManager;
use Silex\Provider\DoctrineServiceProvider;

/**
 * Custom implementation of the Doctrine DBAL Provider.
 */
class KeepAliveDoctrineServiceProvider extends DoctrineServiceProvider
{
    public function register(Container $app)
    {
        parent::register($app);

        $app['dbs'] = function ($app) {
            $app['dbs.options.initializer']();

            $dbs = new Container();
            foreach ($app['dbs.options'] as $name => $options) {
                if ($app['dbs.default'] === $name) {
                    // we use shortcuts here in case the default has been overridden
                    $config = $app['db.config'];
                    $manager = $app['db.event_manager'];
                } else {
                    $config = $app['dbs.config'][$name];
                    $manager = $app['dbs.event_manager'][$name];
                }

                $dbs[$name] = function ($dbs) use ($name, $options, $config, $manager, $app) {
                    $connection = DriverManager::getConnection($options, $config, $manager);

                    $keepAlive = $app['db.keep_alive'];
                    // Add connection to keep alive.
                    if (!$keepAlive->hasConnection($name)) {
                        $keepAlive->addConnection($name, $connection);
                    }

                    return $connection;
                };
            }

            return $dbs;
        };

        $app['db.keep_alive'] = function () {
            $keepAlive = new ConnectionKeepAlive();
            $keepAlive->attach();
            return $keepAlive;
        };
    }
}
