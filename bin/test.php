#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use CultuurNet\ProjectAanvraag\Console\Command\ConsumeCommand;
use Silex\Application;
use fiunchinho\Silex\Provider\RabbitServiceProvider;
use fiunchinho\Silex\Command\Consumer;
use Knp\Provider\ConsoleServiceProvider;

$app = new Application();

$app->register(new ConsoleServiceProvider(), array(
    'console.name'              => 'MyApplication',
    'console.version'           => '1.0.0',
    'console.project_directory' => __DIR__
));


$application = $app['console'];
$application->add(new ConsumeCommand('test'));
$application->run();