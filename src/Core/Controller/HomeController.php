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
     * @var string
     */
    protected $homepageDestination;

    public function __construct(string $homepageDestination)
    {
        $this->homepageDestination = $homepageDestination;
    }

    /**
     * Provide redirect to app
     */
    public function index()
    {
        return $this->redirect($this->homepageDestination);
    }
}
