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
     */
    public function loadProjects($start = 0, $max = 20)
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

        $liveKeys = [];
        $testKeys = [];
        /** @var Project $consumer */
        foreach ($localConsumers as $consumer) {
            if ($consumer->getTestConsumerKey()) {
                $testKeys[] = $consumer->getTestConsumerKey();
            }

            if ($consumer->getLiveConsumerKey()) {
                $liveKeys[] = $consumer->getLiveConsumerKey();
            }
        }

       /* if (!empty($testKeys)) {
            $filters = [
                'key' => $testKeys,
            ];
            $testConsumers = $this->culturefeedTest->getServiceConsumers($start, $max, $filters);
        }

        if (!empty($liveKeys)) {
            $filters = [
                'key' => $liveKeys,
            ];
            $liveConsumers = $this->culturefeedTest->getServiceConsumers($start, $max, $filters);
        }*/

        return $localConsumers;
    }

    /**
     * Load the project by id.
     */
    public function loadProject($id)
    {
        /** @var Project $project */
        $project = $this->projectRepository->find($id);

        if (empty($project)) {
            return;
        }

        // First enrich with test info.
        if ($project->getTestConsumerKey()) {
            $consumer = $this->culturefeedLive->getServiceConsumer($project->getLiveConsumerKey());
            $project->enrichWithConsumerInfo($consumer);
            $project->setTestConsumerSecret($consumer->consumerSecret);
        }

        // Live info is leading, enrich latest.
        if ($project->getLiveConsumerKey()) {
            /** @var \CultureFeed_Consumer $consumer */
            $consumer = $this->culturefeedLive->getServiceConsumer($project->getLiveConsumerKey());
            $project->enrichWithConsumerInfo($consumer);
            $project->setLiveConsumerSecret($consumer->consumerSecret);
        }

        // Load the integration type.
        $integrationType = $this->integrationTypeStorage->load($project->getGroupId());
        if ($integrationType) {
            $project->setGroup($integrationType);
        }

        return $project;
    }

}