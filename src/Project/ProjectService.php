<?php

namespace CultuurNet\ProjectAanvraag\Project;

/**
 * Service class for projects.
 */
class ProjectService implements ProjectServiceInterface
{

    /**
     * @var \ICultureFeed
     */
    protected $culturefeedLive;

    /**
     * @var \ICultureFeed
     */
    protected $culturefeedTest;

    /**
     * Construct the project storage.
     */
    public function __construct(\ICultureFeed $cultureFeedLive, \ICultureFeed $cultureFeedTest)
    {
        $this->culturefeedLive = $cultureFeedLive;
        $this->culturefeedTest = $cultureFeedTest;
    }

    /**
     * Load the projects for current user.
     */
    public function loadProjects() {

        // First load based on the projects known in database.
        print_r($this->culturefeedLive->getServiceConsumers(0, 20));
        print_r($this->culturefeedTest->getServiceConsumers(0, 20));


        die();

    }

}