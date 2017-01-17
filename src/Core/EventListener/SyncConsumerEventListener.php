<?php

namespace CultuurNet\ProjectAanvraag\Core\EventListener;

use CultuurNet\ProjectAanvraag\Core\Event\ConsumerTypeInterface;
use CultuurNet\ProjectAanvraag\Core\Event\SyncConsumer;
use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Event listener for syncing a consumer to the local db.
 */
class SyncConsumerEventListener
{
    /**
     * @var EntityRepository
     */
    protected $projectRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $projectConfig;

    /**
     * SyncConsumerEventListener constructor.
     * @param EntityRepository $repository
     * @param EntityManagerInterface $entityManager
     * @param array $projectConfig
     */
    public function __construct(EntityRepository $repository, EntityManagerInterface $entityManager, array $projectConfig)
    {
        $this->projectRepository = $repository;
        $this->entityManager = $entityManager;
        $this->projectConfig = $projectConfig;
    }

    /**
     * Handle the event
     * @param SyncConsumer $event
     */
    public function handle(SyncConsumer $event)
    {
        $consumerData = $event->getConsumerData();

        if (!empty($consumerData['consumerKey'])) {
            /** @var ProjectInterface $project */
            // Find by key
            $project = $this->projectRepository->findOneBy([$event->getType() . 'ConsumerKey' => $consumerData['consumerKey']]);
            if ($project) {
                $project->setName($consumerData['name']);
            } else {
                // Find by name
                $project = $this->projectRepository->findOneBy(['name' => $consumerData['name']]);
            }

            // No project was found
            if (!$project) {
                $project = new Project();
                $project->setName(!empty($consumerData['name']) ? $consumerData['name'] : '');
                $project->setDescription(!empty($consumerData['description']) ? $consumerData['description'] : '');
                $project->setStatus(!empty($consumerData['status']) &&  $consumerData['status'] == 'active' ? ProjectInterface::PROJECT_STATUS_ACTIVE : ProjectInterface::PROJECT_STATUS_BLOCKED);

                $this->entityManager->persist($project);
            }

            // Set key and group
            if ($project) {
                if ($event->getType() == ConsumerTypeInterface::CONSUMER_TYPE_TEST) {
                    $project->setTestConsumerKey($consumerData['consumerKey']);
                } elseif ($event->getType() == ConsumerTypeInterface::CONSUMER_TYPE_LIVE) {
                    $project->setLiveConsumerKey($consumerData['consumerKey']);
                }

                // Attempt to set the group id
                if (!empty($this->projectConfig['categories'])) {
                    $groupIds = array_keys($this->projectConfig['categories']);
                    $groupIds = array_intersect($groupIds, !empty($consumerData['group']) ? $consumerData['group'] : []);

                    $project->setGroupId(!empty($groupIds) ? reset($groupIds) : null);
                }
            }

            $this->entityManager->flush();
        }
    }
}
