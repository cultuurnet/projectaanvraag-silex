<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Project\Controller\ImportProjectController;
use CultuurNet\ProjectAanvraag\Project\Controller\OpenProjectController;
use CultuurNet\ProjectAanvraag\Project\Controller\ProjectController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * @file
 */
class ProjectControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $app['project_controller'] = function (Application $app) {
            return new ProjectController(
                $app['command_bus'],
                $app['project_service'],
                $app['security.authorization_checker'],
                $app['coupon_validator'],
                $app['legacy_insightly_client'],
                $app['insightly_client'],
                $app['use_new_insightly_instance']
            );
        };

        $app['import_project_controller'] = function (Application $app) {
            return new ImportProjectController(
                $app['command_bus'],
                $app['project_repository']
            );
        };

        $app['open_project_controller'] = function (Application $app) {
            return new OpenProjectController(
                $app['uitid_user_session_service'],
                $app['session'],
                $app['project_repository'],
                $app['config']['platform_host'],
                $app['config']['widget_host']
            );
        };

        /* @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'project_controller:getProjects');
        $controllers->get('/{id}', 'project_controller:getProject');
        $controllers->post('/', 'project_controller:createProject');
        $controllers->delete('/{id}', 'project_controller:deleteProject');
        $controllers->post('/{id}/request-activation', 'project_controller:requestActivation');
        $controllers->get('/{id}/activate', 'project_controller:activateProject');
        $controllers->get('/{id}/block', 'project_controller:blockProject');
        $controllers->put('/{id}/content-filter', 'project_controller:updateContentFilter');
        $controllers->get('/{id}/organisation', 'project_controller:getOrganisation');
        $controllers->put('/{id}/organisation', 'project_controller:updateOrganisation');

        $controllers->post('/{uuid}', 'import_project_controller:importProject');
        $controllers->get('/{id}/widget/', 'open_project_controller:openProject');

        return $controllers;
    }
}
