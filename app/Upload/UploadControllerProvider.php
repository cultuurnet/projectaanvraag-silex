<?php

namespace CultuurNet\ProjectAanvraag\Upload;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class UploadControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['upload_controller'] = function (Application $app) {
            return new UploadController();
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        $controllers->post('upload', 'upload_controller:upload');

        return $controllers;
    }
}
