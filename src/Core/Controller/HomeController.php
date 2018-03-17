<?php

namespace CultuurNet\ProjectAanvraag\Core\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a controller to render widget pages and widgets.
 */
class HomeController extends Controller
{
    /**
     * @var Application
     */
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Provide redirect to app
     */
    public function index()
    {
        $redirect = $this->app['config']['app_host'];
        return $this->redirect($redirect);
    }
}
