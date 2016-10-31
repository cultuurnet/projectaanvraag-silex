<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

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
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Handle the command
     * @param ProjectDeleted $projectDeleted
     * @throws \Exception
     */
    public function handle($projectDeleted)
    {
        $test = 1;
    }
}
