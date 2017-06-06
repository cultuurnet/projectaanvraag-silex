<?php
use CultuurNet\ProjectAanvraag\WebApplication;

phpinfo();die();

require_once __DIR__.'/../vendor/autoload.php';

$app = new WebApplication();
$app->run();
