<?php

namespace CultuurNet\ProjectAanvraag\EventListener;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectDeleted;
use Doctrine\ORM\EntityManagerInterface;

class ProjectDeletedEventListener
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * CreateProjectCommandHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * Handle the command
     * @param ProjectDeleted $projectDeleted
     * @throws \Exception
     */
    public function handle($projectDeleted)
    {
        $project = new Project();
        $project->setName('test');
        $project->setDescription('test');
        $project->setStatus('a');
        $project->setTestConsumerKey('q');
        $project->setTestConsumerSecret('a');
        $project->setGroupId('a');
        $project->setUserId(1);

        $this->entityManager->persist($project);
        $this->entityManager->flush();
    }

}
