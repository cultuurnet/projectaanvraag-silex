<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\FirstName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\LastName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class InsightlyClientTest extends TestCase
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    /**
     * @var Id|null
     */
    private $contactId;

    /**
     * @var Id|null
     */
    private $opportunityId;

    /**
     * @var Id|null
     */
    private $projectId;

    protected function setUp(): void
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../../config.yml'));

        $this->insightlyClient = new InsightlyClient(
            new Client(
                [
                    'base_uri' => $config['integrations']['insightly']['host'],
                    'http_errors' => false,
                ]
            ),
            $config['integrations']['insightly']['api_key'],
            new PipelineStages($config['integrations']['insightly']['pipelines'])
        );

        // Reset ids before every test run
        $this->contactId = null;
        $this->opportunityId = null;
        $this->projectId = null;
    }

    /**
     * @test
     */
    public function it_can_manage_contacts(): void
    {
        $expectedContact = new Contact(
            new FirstName('Jane'),
            new LastName('Doe'),
            new Email('jane.doe@anonymous.com')
        );

        $this->contactId = $this->insightlyClient->createContact($expectedContact);

        $actualContact = $this->insightlyClient->getContactById($this->contactId);
        $this->assertEquals(
            $expectedContact->withId($this->contactId),
            $actualContact
        );

        $this->insightlyClient->deleteContactById($this->contactId);

        $this->expectException(RecordNotFound::class);
        $this->insightlyClient->getContactById($this->contactId);
    }

    /**
     * @test
     */
    public function it_can_manage_opportunities(): void
    {
        $expectedOpportunity = new Opportunity(
            new Name('Opportunity Jane'),
            OpportunityState::open(),
            OpportunityStage::test(),
            new Description('This is the opportunity for a project for Jane Doe'),
            IntegrationType::searchV3()
        );

        $this->opportunityId = $this->insightlyClient->createOpportunity($expectedOpportunity);

        $actualOpportunity = $this->insightlyClient->getOpportunityById($this->opportunityId);
        $this->assertEquals(
            $expectedOpportunity->withId($this->opportunityId),
            $actualOpportunity
        );

        $this->insightlyClient->deleteOpportunityById($this->opportunityId);

        $this->expectException(RecordNotFound::class);
        $this->insightlyClient->getOpportunityById($this->opportunityId);
    }

    /**
     * @test
     */
    public function it_can_manage_projects(): void
    {
        $expectedProject = new Project(
            new Name('Project Jane'),
            ProjectStage::live(),
            ProjectStatus::inProgress(),
            new Description('This is the project for Jane Doe'),
            IntegrationType::searchV3(),
            new Coupon('coupon_code')
        );

        $this->projectId = $this->insightlyClient->createProject($expectedProject);

        $actualProject = $this->insightlyClient->getProjectById($this->projectId);
        $this->assertEquals(
            $expectedProject->withId($this->projectId),
            $actualProject
        );

        $this->insightlyClient->deleteProjectById($this->projectId);

        $this->expectException(RecordNotFound::class);
        $this->insightlyClient->getProjectById($this->projectId);
    }

    protected function onNotSuccessfulTest(\Throwable $t): void
    {
        if ($this->contactId instanceof Id) {
            $this->insightlyClient->deleteContactById($this->contactId);
        }

        if ($this->opportunityId instanceof Id) {
            $this->insightlyClient->deleteOpportunityById($this->opportunityId);
        }

        if ($this->projectId instanceof Id) {
            $this->insightlyClient->deleteProjectById($this->projectId);
        }

        parent::onNotSuccessfulTest($t);
    }
}
