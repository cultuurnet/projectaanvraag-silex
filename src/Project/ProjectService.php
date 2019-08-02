<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\MongoDB\Connection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

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
     * @var \Doctrine\ORM\EntityRepository
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
     * @var Connection
     */
    protected $mongodbConnection;

    /**
     * Construct the project storage.
     * @param \ICultureFeed $cultureFeedLive
     * @param \ICultureFeed $cultureFeedTest
     * @param EntityRepository $repository
     * @param IntegrationTypeStorageInterface $integrationTypeStorage
     * @param User $user
     */
    public function __construct(\ICultureFeed $cultureFeedLive, \ICultureFeed $cultureFeedTest, EntityRepository $repository, IntegrationTypeStorageInterface $integrationTypeStorage, User $user, Connection $mongodbConnection)
    {
        $this->culturefeedLive = $cultureFeedLive;
        $this->culturefeedTest = $cultureFeedTest;
        $this->integrationTypeStorage = $integrationTypeStorage;
        $this->projectRepository = $repository;
        $this->user = $user;
        $this->mongodbConnection = $mongodbConnection;
    }

    /**
     * @inheritdoc
     */
    public function searchProjects($start = 0, $max = 20, $name = '')
    {
        $query = $this->projectRepository->createQueryBuilder('p');

        $expr = Criteria::expr();
        $criteria = Criteria::create();

        // Add limits.
        $criteria->setFirstResult($start)
            ->setMaxResults($max);

        if (!$this->user->isAdmin()) {
            $criteria->where($expr->eq('p.userId', $this->user->id));
        }

        // Searching on name? add search filter.
        if (!empty($name)) {
            $criteria->andWhere($expr->contains('p.name', $name));
        }

        $query->addCriteria($criteria);
        $query->orderBy('p.created', 'DESC');

        // First load based on the projects known in database.
        $localConsumers = $query->getQuery()->getResult();

        // Get total results.
        try {
            $totalResults = $query
                ->select('count(p.id)')
                ->setFirstResult(0)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $totalResults = 0;
        }

        return [
            'total' => $totalResults,
            'results' => $localConsumers,
        ];
    }

    /**
     * @inheritdoc
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

        // Load the integration type.
        $integrationType = $this->integrationTypeStorage->load($project->getGroupId());

        // First enrich with test info.
        if ($project->getTestConsumerKey()) {
            try {
                $consumer = $this->culturefeedTest->getServiceConsumer($project->getTestConsumerKey());
                $project->enrichWithConsumerInfo($consumer, $integrationType->getSapiVersion() ? $integrationType->getSapiVersion() : '2');
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
                $project->enrichWithConsumerInfo($consumer, $integrationType->getSapiVersion() ? $integrationType->getSapiVersion() : '2');
                $project->setLiveConsumerSecret($consumer->consumerSecret);
            } catch (\Exception $e) {
                // Culturefeed http errors fail silently. No enrichment will be done.
                if (!($e instanceof \CultureFeed_HttpException)) {
                    throw $e;
                }
            }
        }

        if ($integrationType) {
            $project->setGroup($integrationType);

            // For a widgets project, we should set the total widgets connected with it.
            if ($integrationType->getActionButton() == IntegrationType::ACTION_BUTTON_WIDGETS) {
                $project->setTotalWidgets($this->getTotalWidgetsForProject($project));
            }

            $project->setSapiVersion($integrationType->getSapiVersion());
        }

        return $project;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContentFilter(ProjectInterface $project, $contentFilter)
    {

        $project->setContentFilter($contentFilter);

        if ($project->getLiveConsumerKey()) {
            $liveConsumerKey = $project->getLiveConsumerKey();
            $liveConsumer = $this->culturefeedLive->getServiceConsumer($liveConsumerKey);
            if ($project->getSapiVersion() == '3') {
                $liveConsumer->searchPrefixSapi3 = $project->getContentFilter();
            } else {
                $liveConsumer->searchPrefixFilterQuery = $project->getContentFilter();
            }
            $this->culturefeedLive->updateServiceConsumer($liveConsumer);
        }

        if ($project->getTestConsumerKey()) {
            $testConsumerKey = $project->getTestConsumerKey();
            $testConsumer = $this->culturefeedTest->getServiceConsumer($testConsumerKey);
            if ($project->getSapiVersion() == '3') {
                $testConsumer->searchPrefixSapi3 = $project->getContentFilter();
            } else {
                $testConsumer->searchPrefixFilterQuery = $project->getContentFilter();
            }
            $this->culturefeedTest->updateServiceConsumer($testConsumer);
        }
    }

    /**
     * Get the total widgets for this project.
     */
    private function getTotalWidgetsForProject(ProjectInterface $project)
    {
        $collection = $this->mongodbConnection->selectCollection('widgets', 'WidgetPage');
        return $collection->count(['project_id' => (string) $project->getId()]);
    }
}
