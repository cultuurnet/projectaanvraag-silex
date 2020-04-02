<?php

use CultuurNet\ProjectAanvraag\Config\ConfigFactory;
use CultuurNet\ProjectAanvraag\ContainerFactory;
use CultuurNet\ProjectAanvraag\ErrorHandling\ErrorHandlerFactory;

require_once __DIR__.'/../vendor/autoload.php';

$config = ConfigFactory::create(__DIR__ . '/../');

$container = ContainerFactory::forWeb($config);

$errorHandler = ErrorHandlerFactory::forWeb($config->get('debug'));
$errorHandler->register();

