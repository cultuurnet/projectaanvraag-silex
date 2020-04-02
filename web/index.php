<?php

use CultuurNet\ProjectAanvraag\Config\ConfigFactory;
use CultuurNet\ProjectAanvraag\ErrorHandling\ErrorHandlerFactory;

require_once __DIR__.'/../vendor/autoload.php';

$config = ConfigFactory::create(__DIR__ . '/../');
$errorHandler = ErrorHandlerFactory::forWeb($config->get('debug'));
