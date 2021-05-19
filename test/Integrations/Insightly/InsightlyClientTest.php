<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Address;
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
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Project;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStage;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\ProjectStatus;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\TaxNumber;
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

    /**
     * @var Id|null
     */
    private $organizationId;

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

        // Reset ids before every test run and cleanup with the teardown
        $this->contactId = null;
        $this->opportunityId = null;
        $this->projectId = null;
        $this->organizationId = null;
    }

    protected function tearDown(): void
    {
        if ($this->contactId instanceof Id) {
            $this->insightlyClient->contacts()->deleteById($this->contactId);
        }

        if ($this->opportunityId instanceof Id) {
            $this->insightlyClient->opportunities()->deleteById($this->opportunityId);
        }

        if ($this->projectId instanceof Id) {
            $this->insightlyClient->projects()->deleteById($this->projectId);
        }

        if ($this->organizationId instanceof Id) {
            $this->insightlyClient->organizations()->deleteById($this->organizationId);
        }
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

        $this->contactId = $this->insightlyClient->contacts()->create($expectedContact);

        $actualContact = $this->insightlyClient->contacts()->getByEmail($expectedContact->getEmail());
        $this->assertEquals(
            $expectedContact->withId($this->contactId),
            $actualContact
        );
    }

    /**
     * @test
     */
    public function it_throws_when_contact_not_found(): void
    {
        $this->expectException(RecordNotFound::class);

        $this->insightlyClient->contacts()->getByEmail(new Email('jane.doe@anonymous.com'));
    }

    /**
     * @test
     */
    public function it_can_manage_opportunities(): void
    {
        $this->contactId = $this->insightlyClient->contacts()->create(
            new Contact(
                new FirstName('Jane'),
                new LastName('Doe'),
                new Email('jane.doe@anonymous.com')
            )
        );

        $expectedOpportunity = new Opportunity(
            new Name('Opportunity Jane'),
            OpportunityState::open(),
            OpportunityStage::test(),
            new Description('This is the opportunity for a project for Jane Doe'),
            IntegrationType::searchV3()
        );

        $this->opportunityId = $this->insightlyClient->opportunities()->createWithContact(
            $expectedOpportunity,
            $this->contactId
        );

        // When a create is done on Insightly not all objects are stored immediately
        // When getting the created object it can happen some parts like linked contact and custom fields are still missing
        // This sleep will fix that 😬
        sleep(1);

        $actualOpportunity = $this->insightlyClient->opportunities()->getById($this->opportunityId);
        $this->assertEquals(
            $expectedOpportunity->withId($this->opportunityId),
            $actualOpportunity
        );
    }

    /**
     * @test
     */
    public function it_can_manage_projects(): void
    {
        $this->contactId = $this->insightlyClient->contacts()->create(
            new Contact(
                new FirstName('Jane'),
                new LastName('Doe'),
                new Email('jane.doe@anonymous.com')
            )
        );

        $expectedProject = (new Project(
            new Name('Project Jane'),
            ProjectStage::live(),
            ProjectStatus::inProgress(),
            new Description('This is the project for Jane Doe'),
            IntegrationType::searchV3()
        ))->withCoupon(new Coupon('coupon_code'));

        $this->projectId = $this->insightlyClient->projects()->createWithContact($expectedProject, $this->contactId);

        sleep(1);

        $actualProject = $this->insightlyClient->projects()->getById($this->projectId);
        $this->assertEquals(
            $expectedProject->withId($this->projectId),
            $actualProject
        );
    }

    /**
     * @test
     */
    public function it_can_manage_organizations(): void
    {
        $expectedOrganization = new Organization(
            new Name('Anonymous'),
            new Address(
                'Street without a name 000',
                '1234',
                'Nowhere town'
            ),
            new Email('account@anonymous.com')
        );

        $this->organizationId = $this->insightlyClient->organizations()->create($expectedOrganization);

        sleep(1);

        $actualProject = $this->insightlyClient->organizations()->getByEmail($expectedOrganization->getEmail());
        $this->assertEquals(
            $expectedOrganization->withId($this->organizationId),
            $actualProject
        );
    }

    /**
     * @test
     */
    public function it_can_manage_organizations_with_tax_number(): void
    {
        $expectedOrganization = (new Organization(
            new Name('Anonymous'),
            new Address(
                'Street without a name 000',
                '1234',
                'Nowhere town'
            ),
            new Email('account@anonymous.com')
        ))->withTaxNumber(new TaxNumber('BE123456789'));

        $this->organizationId = $this->insightlyClient->organizations()->create($expectedOrganization);

        sleep(1);

        $actualProject = $this->insightlyClient->organizations()->getByTaxNumber($expectedOrganization->getTaxNumber());
        $this->assertEquals(
            $expectedOrganization->withId($this->organizationId),
            $actualProject
        );
    }
}
