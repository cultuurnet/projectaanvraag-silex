<?php

namespace CultuurNet\ProjectAanvraag\Upload;

use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use CultuurNet\ProjectAanvraag\Upload\UploadController;

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
