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
        }
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setStatus($importProject->getState());

        // The client ids are the only way widgets authenticate against search api 3, so keep them in sync on
        // every import instead of only on creation.
        $project->setTestClientId($importProject->getTestClientId());
        $project->setLiveClientId($importProject->getLiveClientId());

        // Integrations without UiTiDv1 consumers report no search api 3 keys. Never let such an import wipe the
        // keys of a project that still has working ones.
        if ($importProject->getTestApiKeySapi3() !== null) {
            $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        }
        if ($importProject->getLiveApiKeySapi3() !== null) {
            $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        }

        $this->entityManager->persist($project);

        $this->entityManager->flush();

        $this->logger->debug('Finished handling ImportProject for ' . $importProject->getName());
    }
}
