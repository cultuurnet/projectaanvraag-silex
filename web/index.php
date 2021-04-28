<?php
use CultuurNet\ProjectAanvraag\WebApplication;

//tideways_enable(TIDEWAYS_FLAGS_NO_SPANS);

define('WWW_ROOT', realpath(__DIR__));

require_once __DIR__.'/../vendor/autoload.php';

global $app;
$app = new WebApplication();
//$app['http_cache']->run();
$app->run();

/*$data = tideways_disable();
file_put_contents(
    sys_get_temp_dir() . "/" . uniqid() . ".projectaanvraag.xhprof",
    serialize($data)
);*/

// Detach running DBAL KeepAlive instances.
$app['db.keep_alive']->detach();
