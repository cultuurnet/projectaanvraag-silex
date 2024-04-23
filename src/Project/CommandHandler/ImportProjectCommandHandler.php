<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ImportProjectCommandHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function handle(ImportProject $importProject): void
    {
        $this->logger->debug('Start handling ImportProject for ' . $importProject->getName());

        $project = $this->entityManager->getRepository('ProjectAanvraag:Project')->findOneBy(['platformUuid' => $importProject->getPlatformUuid()]);
        if ($project === null) {
            $project = new Project();
            $project->setUserId($importProject->getUserId());
            $project->setGroupId($importProject->getGroupId());
            $project->setPlatformUuid($importProject->getPlatformUuid());
            $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
            $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        }
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setStatus($importProject->getState());

        $this->entityManager->persist($project);

        $this->entityManager->flush();

        $this->logger->debug('Finished handling ImportProject for ' . $importProject->getName());
    }
}
