<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\ProjectAanvraag\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

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
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $projectRepository;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var IntegrationTypeStorageInterface
     */
    protected $integrationTypeStorage;

    /**
     * Construct the project storage.
     */
    public function __construct(\ICultureFeed $cultureFeedLive, \ICultureFeed $cultureFeedTest, EntityManagerInterface $entityManager, IntegrationTypeStorageInterface $integrationTypeStorage, User $user)
    {
        $this->culturefeedLive = $cultureFeedLive;
        $this->culturefeedTest = $cultureFeedTest;
        $this->projectRepository = $entityManager->getRepository('ProjectAanvraag:Project');
        $this->integrationTypeStorage = $integrationTypeStorage;
        $this->user = $user;
    }

    /**
     * Load the projects for current user.
     * @param int $max
     * @param int $start
     * @return array
     */
    public function loadProjects($max = 20, $start = 0)
    {
        $criteria = [];
        if (!$this->user->isAdmin()) {
            $criteria = ['userId' => $this->user->id];
        }

        // First load based on the projects known in database.
        $localConsumers = $this->projectRepository->findBy(
            $criteria,
            ['created' => 'DESC'],
            $max,
            $start
        );

        return $localConsumers;
    }

    /**
     * Load the project by id.
     * @param $id
     * @return Project
     * @throws \Exception
     */
    public function loadProject($id)
    {
        $criteria = [
            'id' => $id,
        ];

        /** @var Project $project */
        $project = $this->projectRepository->findOneBy($criteria);

        if (empty($project)) {
            return;
        }

        // First enrich with test info.
        if ($project->getTestConsumerKey()) {
            try {
                $consumer = $this->culturefeedTest->getServiceConsumer($project->getTestConsumerKey());
                $project->enrichWithConsumerInfo($consumer);
                $project->setTestConsumerSecret($consumer->consumerSecret);
            } catch (\Exception $e) {
                // Culturefeed http errors fail silently. No enrichment will be done.
                if (!($e instanceof \CultureFeed_HttpException)) {
                    throw $e;
                }
            }
        }

        // Live info is leading, enrich latest.
        if ($project->getLiveConsumerKey()) {
            try {
                /** @var \CultureFeed_Consumer $consumer */
                $consumer = $this->culturefeedLive->getServiceConsumer($project->getLiveConsumerKey());
                $project->enrichWithConsumerInfo($consumer);
                $project->setLiveConsumerSecret($consumer->consumerSecret);
            } catch (\Exception $e) {
                // Culturefeed http errors fail silently. No enrichment will be done.
                if (!($e instanceof \CultureFeed_HttpException)) {
                    throw $e;
                }
            }
        }

        // Load the integration type.
        $integrationType = $this->integrationTypeStorage->load($project->getGroupId());
        if ($integrationType) {
            $project->setGroup($integrationType);
        }

        return $project;
    }
}
