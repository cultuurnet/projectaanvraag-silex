<?php

namespace CultuurNet\ProjectAanvraag\Project\EventListener;

use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use CultuurNet\ProjectAanvraag\Project\Event\ProjectCreated;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class ProjectCreatedEventListenerTest extends TestCase
{
    /**
     * @var InsightlyClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyClient;

    /**
     * @var ProjectActivatedEventListener
     */
    protected $eventListener;

    /**
     * @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

    /**
     * @var Project|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyProject;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \CultuurNet\ProjectAanvraag\Entity\User|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localUser;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->insightlyClient = $this->createMock(InsightlyClientInterface::class);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->eventListener = new ProjectCreatedEventListener(
            $this->insightlyClient,
            [
                'pipeline' => 1,
                'categories' => [
                    2 => 'category',
                ],
                'custom_fields' => [
                    'live_key' => 'live',
                    'test_key' => 'test',
                    'coupon' => 'coupon',
                ],
                'stages' => [
                    'live_met_coupon' => 'coupon',
                    'test' => 'no coupon',
                ],
            ],
            $this->entityManager
        );

        $this->project = new \CultuurNet\ProjectAanvraag\Entity\Project();
        $this->project->setId(1);
        $this->project->setName('name');
        $this->project->setGroupId(2);
        $this->project->setDescription('description');

        // User should get updated.
        $this->localUser = new \CultuurNet\ProjectAanvraag\Entity\User(1);
        $this->localUser->setLastName('lastname');
        $this->localUser->setFirstName('firstname');
        $this->localUser->setEmail('email@email.com');

        $repository = $this->createMock(EntityRepository::class);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('ProjectAanvraag:Project')
            ->willReturn($repository);

        $repository->expects($this->any())
            ->method('find')
            ->with(1)
            ->willReturn($this->project);
    }

    /**
     * Test the event listener handler
     */
    public function testHandle()
    {
        $this->project->setTestConsumerKey('testkey');
        $this->setupContact();

        // Project should be created with all info.
        $insightlyProject = new Project();
        $insightlyProject->setName('name');
        $insightlyProject->setStatus(Project::STATUS_IN_PROGRESS);
        $insightlyProject->setCategoryId('category');
        $insightlyProject->setDetails('description');

        $link = new Link();
        $link->setContactId(1);
        $link->setRole('Aanvrager');
        $insightlyProject->addLink($link);
        $insightlyProject->addCustomField('test', 'testkey');

        $createdInsightlyProject = clone $insightlyProject;
        $createdInsightlyProject->setId(1);

        $this->insightlyClient->expects($this->once())
            ->method('createProject')
            ->with($insightlyProject)
            ->willReturn($createdInsightlyProject);

        // The pipeline should be updated.
        $this->insightlyClient->expects($this->once())
            ->method('updateProjectPipeline')
            ->with(1, 1, 'no coupon');

        // The project should be updated.
        $updatedProject = clone $this->project;
        $updatedProject->setInsightlyProjectId(1);
        $this->entityManager->expects($this->at(3))
            ->method('merge')
            ->with($updatedProject);
        $this->entityManager->expects($this->at(4))
            ->method('flush');

        $projectCreated = new ProjectCreated($this->project, $this->localUser);
        $this->eventListener->handle($projectCreated);
    }

    /**
     * Test the handler when a coupon was given.
     */
    public function testHandleWithCoupon()
    {
        $this->project->setLiveConsumerKey('livekey');
        $this->project->setTestConsumerKey('testkey');
        $this->setupContact();

        // Project should be created with all info.
        $insightlyProject = new Project();
        $insightlyProject->setName('name');
        $insightlyProject->setStatus(Project::STATUS_COMPLETED);
        $insightlyProject->setCategoryId('category');
        $insightlyProject->setDetails('description');

        $link = new Link();
        $link->setContactId(1);
        $link->setRole('Aanvrager');
        $insightlyProject->addLink($link);
        $insightlyProject->addCustomField('test', 'testkey');
        $insightlyProject->addCustomField('live', 'livekey');
        $insightlyProject->addCustomField('coupon', 'coupon');

        $createdInsightlyProject = clone $insightlyProject;
        $createdInsightlyProject->setId(1);

        $this->insightlyClient->expects($this->once())
            ->method('createProject')
            ->with($insightlyProject)
            ->willReturn($createdInsightlyProject);

        // The pipeline should be updated.
        $this->insightlyClient->expects($this->once())
            ->method('updateProjectPipeline')
            ->with(1, 1, 'coupon');

        // The project should be updated.
        $updatedProject = clone $this->project;
        $updatedProject->setInsightlyProjectId(1);
        $this->entityManager->expects($this->at(3))
            ->method('merge')
            ->with($updatedProject);
        $this->entityManager->expects($this->at(4))
            ->method('flush');

        $projectCreated = new ProjectCreated($this->project, $this->localUser, 'coupon');
        $this->eventListener->handle($projectCreated);
    }

    /**
     * Test the handler the user already exists.
     */
    public function testHandleWithExistingAccount()
    {
        $this->project->setTestConsumerKey('testkey');
        $contact = new Contact();
        $contact->setId(20);
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'email@email.com');

        $this->localUser->setInsightylContactId(20);
        $this->localUser->setEmail('email@email.com');
        $this->insightlyClient->expects($this->once())
            ->method('getContactByEmail')
            ->willReturn($contact);

        $this->insightlyClient
            ->expects($this->never())
            ->method('createContact');

        // Project should be created with all info.
        $insightlyProject = new Project();
        $insightlyProject->setName('name');
        $insightlyProject->setStatus(Project::STATUS_IN_PROGRESS);
        $insightlyProject->setCategoryId('category');
        $insightlyProject->setDetails('description');

        $link = new Link();
        $link->setContactId(20);
        $link->setRole('Aanvrager');
        $insightlyProject->addLink($link);
        $insightlyProject->addCustomField('test', 'testkey');

        $createdInsightlyProject = clone $insightlyProject;
        $createdInsightlyProject->setId(1);

        $this->insightlyClient->expects($this->once())
            ->method('createProject')
            ->with($insightlyProject)
            ->willReturn($createdInsightlyProject);

        $projectCreated = new ProjectCreated($this->project, $this->localUser);
        $this->eventListener->handle($projectCreated);
    }

    /**
     * Test the handler when the user already exists, but not in insightly.
     */
    public function testHandleWithNonExistingAccount()
    {
        $this->setupContact();
        $this->project->setTestConsumerKey('testkey');
        $contact = new Contact();
        $contact->setId(20);

        $this->localUser->setInsightylContactId(20);
        $this->insightlyClient->expects($this->once())
            ->method('getContactByEmail')
            ->willReturn([]);

        $insightlyProject = new Project();
        $this->insightlyClient->expects($this->once())
            ->method('createProject')
            ->willReturn($insightlyProject);

        $projectCreated = new ProjectCreated($this->project, $this->localUser);
        $this->eventListener->handle($projectCreated);
    }

    /**
     * General setup function for new projects with a new contact.
     */
    private function setupContact()
    {

        $updatedLocalUser = clone $this->localUser;
        $updatedLocalUser->setInsightylContactId(1);

        $this->entityManager->expects($this->at(0))
            ->method('merge')
            ->with($updatedLocalUser);
        $this->entityManager->expects($this->at(1))
            ->method('flush');

        // Contact should be created.
        $contact = new Contact();
        $contact->setFirstName('firstname');
        $contact->setLastName('lastname');
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'email@email.com');
        $savedContact = clone $contact;
        $savedContact->setId(1);

        $this->insightlyClient
            ->expects($this->once())
            ->method('createContact')
            ->with($contact)
            ->willReturn($savedContact);
    }
}
