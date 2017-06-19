<?php
use CultuurNet\ProjectAanvraag\WebApplication;

require_once __DIR__.'/../vendor/autoload.php';

global $app;
$app = new WebApplication();
$app->run();
