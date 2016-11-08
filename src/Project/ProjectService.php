<?php

namespace CultuurNet\ProjectAanvraag\Project;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\Common\Collections\Criteria;
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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Construct the project storage.
     * @param \ICultureFeed $cultureFeedLive
     * @param \ICultureFeed $cultureFeedTest
     * @param EntityManagerInterface $entityManager
     * @param IntegrationTypeStorageInterface $integrationTypeStorage
     * @param User $user
     */
    public function __construct(\ICultureFeed $cultureFeedLive, \ICultureFeed $cultureFeedTest, EntityManagerInterface $entityManager, IntegrationTypeStorageInterface $integrationTypeStorage, User $user)
    {
        $this->culturefeedLive = $cultureFeedLive;
        $this->culturefeedTest = $cultureFeedTest;
        $this->entityManager = $entityManager;
        $this->integrationTypeStorage = $integrationTypeStorage;
        $this->projectRepository = $entityManager->getRepository('ProjectAanvraag:Project');
        $this->user = $user;
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

        // First load based on the projects known in database.
        $localConsumers = $query->getQuery()->getResult();

        // Get total results.
        $totalResults = $query
            ->select('count(p.id)')
            ->setFirstResult(0)
            ->getQuery()
            ->getSingleScalarResult();

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
