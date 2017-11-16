<?php
use CultuurNet\ProjectAanvraag\WebApplication;

require_once __DIR__.'/../vendor/autoload.php';

$app = new WebApplication();
$app->run();

// Detach running DBAL KeepAlive instances.
$app['db.keep_alive']->detach();
