<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Project\Command\ImportProject;
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
     * @var LoggerInterface & MockObject
     */
    private $logger;

    /**
     * @var EntityRepository & MockObject
     */
    private $projectRepository;

    /**
     * @var ImportProjectCommandHandler
     */
    private $importProjectCommandHandler;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->importProjectCommandHandler = new ImportProjectCommandHandler(
            $this->entityManager,
            $this->logger
        );

        $this->projectRepository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('ProjectAanvraag:Project')
            ->willReturn($this->projectRepository);
    }

    public function testHandleNewImport(): void
    {
        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['platformUuid' => '0d228560-8cc6-4303-8fd1-c404e6fd79fd'])
            ->willReturn(null);

        $importProject = new ImportProject(
            '0d228560-8cc6-4303-8fd1-c404e6fd79fd',
            'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d',
            'Imported widget project',
            'This is a widget project imported from publiq-platform',
            24378,
            'SAPI3 test key',
            'SAPI3 live key',
            'Test client id',
            'Live client id',
            'active'
        );

        $project = new Project();
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setGroupId($importProject->getGroupId());
        $project->setUserId($importProject->getUserId());
        $project->setPlatformUuid($importProject->getPlatformUuid());
        $project->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $project->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        $project->setTestClientId($importProject->getTestClientId());
        $project->setLiveClientId($importProject->getLiveClientId());
        $project->setStatus(Project::PROJECT_STATUS_ACTIVE);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($project);

        $this->importProjectCommandHandler->handle($importProject);
    }

    public function testHandleUpdateImport(): void
    {
        $importProject = new ImportProject(
            '0d228560-8cc6-4303-8fd1-c404e6fd79fd',
            'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d',
            'Imported widget project',
            'This is a widget project imported from publiq-platform',
            24378,
            'SAPI3 test key',
            'SAPI3 live key',
            'Test client id',
            'Live client id',
            'active'
        );

        $projectToBeUpdated = new Project();
        $projectToBeUpdated->setId(123);
        $projectToBeUpdated->setName('old name');
        $projectToBeUpdated->setDescription('old description');
        $projectToBeUpdated->setGroupId($importProject->getGroupId());
        $projectToBeUpdated->setUserId($importProject->getUserId());
        $projectToBeUpdated->setPlatformUuid($importProject->getPlatformUuid());
        $projectToBeUpdated->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $projectToBeUpdated->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        $projectToBeUpdated->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        $updatedProject = new Project();
        $updatedProject->setId(123);
        $updatedProject->setName($importProject->getName());
        $updatedProject->setDescription($importProject->getDescription());
        $updatedProject->setGroupId($importProject->getGroupId());
        $updatedProject->setUserId($importProject->getUserId());
        $updatedProject->setPlatformUuid($importProject->getPlatformUuid());
        $updatedProject->setTestApiKeySapi3($importProject->getTestApiKeySapi3());
        $updatedProject->setLiveApiKeySapi3($importProject->getLiveApiKeySapi3());
        $updatedProject->setTestClientId($importProject->getTestClientId());
        $updatedProject->setLiveClientId($importProject->getLiveClientId());
        $updatedProject->setStatus(Project::PROJECT_STATUS_ACTIVE);

        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['platformUuid' => '0d228560-8cc6-4303-8fd1-c404e6fd79fd'])
            ->willReturn($projectToBeUpdated);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($updatedProject);

        $this->importProjectCommandHandler->handle($importProject);
    }

    public function testHandleCreateImportWithoutApiKeys(): void
    {
        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['platformUuid' => '0d228560-8cc6-4303-8fd1-c404e6fd79fd'])
            ->willReturn(null);

        $importProject = new ImportProject(
            '0d228560-8cc6-4303-8fd1-c404e6fd79fd',
            'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d',
            'Imported widget project',
            'This is a widget project imported from publiq-platform',
            24378,
            null,
            null,
            'Test client id',
            'Live client id',
            'active'
        );

        $project = new Project();
        $project->setName($importProject->getName());
        $project->setDescription($importProject->getDescription());
        $project->setGroupId($importProject->getGroupId());
        $project->setUserId($importProject->getUserId());
        $project->setPlatformUuid($importProject->getPlatformUuid());
        $project->setTestClientId($importProject->getTestClientId());
        $project->setLiveClientId($importProject->getLiveClientId());
        $project->setStatus(Project::PROJECT_STATUS_ACTIVE);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($project);

        $this->importProjectCommandHandler->handle($importProject);

        $this->assertNull($project->getTestApiKeySapi3());
        $this->assertNull($project->getLiveApiKeySapi3());
    }

    public function testHandleUpdateImportWithoutApiKeysKeepsTheExistingOnes(): void
    {
        $importProject = new ImportProject(
            '0d228560-8cc6-4303-8fd1-c404e6fd79fd',
            'auth0|39f6bc3d-2ba9-4587-8602-4a00a2b6667d',
            'Imported widget project',
            'This is a widget project imported from publiq-platform',
            24378,
            null,
            null,
            'New test client id',
            'New live client id',
            'active'
        );

        $projectToBeUpdated = new Project();
        $projectToBeUpdated->setId(123);
        $projectToBeUpdated->setName('old name');
        $projectToBeUpdated->setDescription('old description');
        $projectToBeUpdated->setGroupId($importProject->getGroupId());
        $projectToBeUpdated->setUserId($importProject->getUserId());
        $projectToBeUpdated->setPlatformUuid($importProject->getPlatformUuid());
        $projectToBeUpdated->setTestApiKeySapi3('SAPI3 test key');
        $projectToBeUpdated->setLiveApiKeySapi3('SAPI3 live key');
        $projectToBeUpdated->setTestClientId('Old test client id');
        $projectToBeUpdated->setLiveClientId('Old live client id');
        $projectToBeUpdated->setStatus(Project::PROJECT_STATUS_APPLICATION_SENT);

        $this->projectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['platformUuid' => '0d228560-8cc6-4303-8fd1-c404e6fd79fd'])
            ->willReturn($projectToBeUpdated);

        $this->logger->expects($this->exactly(2))
            ->method('debug');

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($projectToBeUpdated);

        $this->importProjectCommandHandler->handle($importProject);

        $this->assertEquals('SAPI3 test key', $projectToBeUpdated->getTestApiKeySapi3());
        $this->assertEquals('SAPI3 live key', $projectToBeUpdated->getLiveApiKeySapi3());
        $this->assertEquals('New test client id', $projectToBeUpdated->getTestClientId());
        $this->assertEquals('New live client id', $projectToBeUpdated->getLiveClientId());
    }
}
