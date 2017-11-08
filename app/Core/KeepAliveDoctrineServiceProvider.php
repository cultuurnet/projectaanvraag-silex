<?php

namespace CultuurNet\ProjectAanvraag\Core;

use CultuurNet\ProjectAanvraag\Core\Doctrine\ConnectionKeepAlive;
use Pimple\Container;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Silex\Provider\DoctrineServiceProvider;

/**
 * Custom implementation of the Doctrine DBAL Provider.
 */
class KeepAliveDoctrineServiceProvider extends DoctrineServiceProvider
{
    public function register(Container $app)
    {
        $app['db.default_options'] = array(
            'driver' => 'pdo_mysql',
            'dbname' => null,
            'host' => 'localhost',
            'user' => 'root',
            'password' => null,
        );

        $app['dbs.options.initializer'] = $app->protect(
            function () use ($app) {
                static $initialized = false;

                if ($initialized) {
                    return;
                }

                $initialized = true;

                if (!isset($app['dbs.options'])) {
                    $app['dbs.options'] = array('default' => isset($app['db.options']) ? $app['db.options'] : array());
                }

                $tmp = $app['dbs.options'];
                foreach ($tmp as $name => &$options) {
                    $options = array_replace($app['db.default_options'], $options);

                    if (!isset($app['dbs.default'])) {
                        $app['dbs.default'] = $name;
                    }
                }
                $app['dbs.options'] = $tmp;
            }
        );

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

        $app['db.keep_alive'] = function ($app) {
            $keepAlive = new ConnectionKeepAlive();
            $keepAlive->attach();
            return $keepAlive;
        };

        $app['dbs.config'] = function ($app) {
            $app['dbs.options.initializer']();

            $configs = new Container();
            $addLogger = isset($app['logger']) && null !== $app['logger'] && class_exists('Symfony\Bridge\Doctrine\Logger\DbalLogger');
            foreach ($app['dbs.options'] as $name => $options) {
                $configs[$name] = new Configuration();
                if ($addLogger) {
                    $configs[$name]->setSQLLogger(new DbalLogger($app['logger'], isset($app['stopwatch']) ? $app['stopwatch'] : null));
                }
            }

            return $configs;
        };

        $app['dbs.event_manager'] = function ($app) {
            $app['dbs.options.initializer']();

            $managers = new Container();
            foreach ($app['dbs.options'] as $name => $options) {
                $managers[$name] = new EventManager();
            }

            return $managers;
        };

        // shortcuts for the "first" DB
        $app['db'] = function ($app) {
            $dbs = $app['dbs'];

            return $dbs[$app['dbs.default']];
        };

        $app['db.config'] = function ($app) {
            $dbs = $app['dbs.config'];

            return $dbs[$app['dbs.default']];
        };

        $app['db.event_manager'] = function ($app) {
            $dbs = $app['dbs.event_manager'];

            return $dbs[$app['dbs.default']];
        };
    }
}
