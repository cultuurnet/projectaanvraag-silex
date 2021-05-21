<?php

namespace CultuurNet\ProjectAanvraag\Project\CommandHandler;

use CultuurNet\ProjectAanvraag\Entity\Project;
use CultuurNet\ProjectAanvraag\Entity\ProjectInterface;
use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use \CultuurNet\ProjectAanvraag\Insightly\Item\Project as InsightlyProject;
use CultuurNet\ProjectAanvraag\Project\Command\RequestActivation;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SimpleBus\Message\Bus\Middleware\MessageBusSupportingMiddleware;

class RequestActivationCommandHandlerTest extends TestCase
{
    /**
     * @var MessageBusSupportingMiddleware & MockObject
     */
    protected $eventBus;

    /**
     * @var EntityManagerInterface & MockObject
     */
    protected $entityManager;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeed;

    /**
     * @var \CultureFeed & MockObject
     */
    protected $cultureFeedTest;

    /**
     * @var ProjectInterface & MockObject
     */
    protected $project;

    /**
     * @var InsightlyProject & MockObject
     */
    protected $insightlyProject;

    /**
     * @var RequestActivationCommandHandler
     */
    protected $commandHandler;

    public function setUp()
    {
        $this->eventBus = $this->createMock(MessageBusSupportingMiddleware::class);

        $this->entityManager = $this->createMock(EntityManager::class);

        $this->entityManager
            ->expects($this->any())
            ->method('flush');

        // Fake a project
        $this->project = new Project();
        $this->project->setInsightlyProjectId(2);

        // Fake an insightly project.
        $this->insightlyProject = new InsightlyProject();
        $this->insightlyProject->setName('name');
        $this->insightlyProject->setId(2);

        $this->commandHandler = new RequestActivationCommandHandler($this->eventBus, $this->entityManager);
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

        // Test if local db is updated.
        $toBeProject = clone $this->project;
        $toBeProject->setStatus(ProjectInterface::PROJECT_STATUS_WAITING_FOR_PAYMENT);
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($toBeProject);

        $this->commandHandler->handle($requestActivation);
    }
}
