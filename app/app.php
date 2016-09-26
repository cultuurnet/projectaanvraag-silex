<?php

use CultuurNet\ProjectAanvraag\Project\ProjectControllerProvider;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config.yml'));

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->mount('/', new ProjectControllerProvider());

$app['debug'] = $app['config']['debug'] === true;

return $app;
