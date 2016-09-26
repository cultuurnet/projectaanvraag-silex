<?php

use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;

require_once __DIR__.'/../vendor/autoload.php';
error_reporting(2147483647);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

$app = new Silex\Application();

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config.yml'));

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->mount('/', new ProjectControllerProvider());

$app['debug'] = $app['config']['debug'] === true;


return $app;