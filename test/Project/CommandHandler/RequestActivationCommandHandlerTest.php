<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\InsightlyClientInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\EntityList;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use \CultuurNet\ProjectAanvraag\Insightly\Item\Project as InsightlyProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use CultuurNet\ProjectAanvraag\User\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class RequestActivationCommandHandlerTest extends TestCase
{
    /**
     * @var MessageBusSupportingMiddleware|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var \CultureFeed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cultureFeed;

    /**
     * @var \CultureFeed|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cultureFeedTest;

    /**
     * @var ProjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

    /**
     * @var InsightlyProject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyProject;

    /**
     * @var InsightlyClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $insightlyClient;

    /**
     * @var array
     */
    protected $insightlyConfig;

    /**
     * @var RequestActivationCommandHandler
     */
    protected $commandHandler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->eventBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        $this->insightlyClient = $this->createMock(InsightlyClientInterface::class);

        $this->insightlyConfig = [
            'pipeline' => 1,
            'stages' => [
                'aanvraag' => 'aanvraag',
            ],
            'custom_fields' => [
                'vat' => 'vatfield',
                'payment' => 'paymentfield',
            ],
        ];

        // Fake a project
        $this->project = new Project();
        $this->project->setInsightlyProjectId(2);

        // Fake an insightly project.
        $this->insightlyProject = new InsightlyProject();
        $this->insightlyProject->setName('name');
        $this->insightlyProject->setId(2);

        // Methods that should be called every test.
        $this->insightlyClient->expects($this->once())
            ->method('getProject')
            ->with(2)
            ->willReturn($this->insightlyProject);

        $this->insightlyClient->expects($this->once())
            ->method('updateProjectPipelineStage')
            ->with(2, $this->insightlyConfig['stages']['aanvraag']);

        $this->commandHandler = new RequestActivationCommandHandler($this->eventBus, $this->entityManager, new User(), $this->insightlyClient, $this->insightlyConfig);
    }

    /**
     * Test the command handler
     */
    public function testRequestWithoutVat()
    {
        $this->requestTest();
    }

    /**
     * Test the command handler with VAT.
     */
    public function testRequestWithVat()
    {
        $this->requestTest('vat');
    }

    /**
     * Test the request handling.
     */
    private function requestTest($vat = '', $payment = '')
    {

        $address = new \CultuurNet\ProjectAanvraag\Address('street number', '9000', 'Gent');
        $requestActivation = new RequestActivation($this->project, 'name', $address, $vat, $payment);

        // Address that should be created.
        $address = new Address();
        $address->setType('WORK');
        $address->setStreet('street number');
        $address->setCity('Gent');
        $address->setPostal('9000');

        // Organisation that should be created.
        $organisation = new Organisation();
        $organisation->setName('name');
        $organisation->getAddresses()->append($address);

        if ($vat) {
            $organisation->addCustomField('vatfield', 'vat');
        }

        if ($payment) {
            $organisation->addCustomField('paymentfield', 'payment');
        }

        $createdOrganisation = clone $organisation;
        $createdOrganisation->setId(1);

        // Test if organisation is created.
        $this->insightlyClient->expects($this->once())
            ->method('createOrganisation')
            ->with($organisation)
            ->willReturn($createdOrganisation);


        // Test if insightly project gets updated.
        $link = new Link();
        $link->setOrganisationId(1);
        $links = new EntityList([$link]);
        $targetInsightlyProject = clone $this->insightlyProject;
        $targetInsightlyProject->setLinks($links);
        $this->insightlyClient->expects($this->once())
            ->method('updateProject')
            ->with($targetInsightlyProject);

        // Test if local db is updated.
        $toBeProject = clone $this->project;
        $toBeProject->setStatus(ProjectInterface::PROJECT_STATUS_WAITING_FOR_PAYMENT);
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($toBeProject);

        $this->commandHandler->handle($requestActivation);
    }
}
