<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\User as UserEntity;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImportProjectCommandHandlerTest extends TestCase
{
    /**
     * @var EntityManagerInterface & MockObject
     */
    private $entityManager;

    /**
     * @var User
     */
    private $user;

    /**
     * @var LoggerInterface & MockObject
     */
    private $logger;

    /**
     * @var ImportProjectCommandHandler
     */
    private $importProjectCommandHandler;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->user = new User();
        $this->user->id = 123;
        $this->user->mbox = 'test@test.be';
        $this->user->nick = 'test';

        $this->importProjectCommandHandler = new ImportProjectCommandHandler(
            $this->entityManager,
            $this->user,
            $this->logger
        );
    }

    public function testHandle(): void
    {
        $importProject = new ImportProject(
            '0d228560-8cc6-4303-8fd1-c404e6fd79fd',
            'Imported widget project',
            'This is a widget project imported from publiq-platform',
            24378,
            'SAPI3 test key',
            'SAPI3 live key'
        );

        $project = new Project();
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setGroupId($importProject->getGroupId());
        $project->setUserId($this->user->id);
        $project->setPlatformUuid($importProject->getPlatformUuid());
        $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        $project->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($project);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('find')
            ->with($this->user->id)
            ->willReturn(new UserEntity(123));

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with('ProjectAanvraag:User')
            ->willReturn($repository);

        $this->importProjectCommandHandler->handle($importProject);
    }
}
