<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Link;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use Guzzle\Http\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class InsightlyIntegrationTest extends TestCase
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

    protected function setUp()
    {
        parent::setUp();

        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../config.yml'));
        $this->insightlyClient = new InsightlyClient(
            new Client($config['insightly']['host']),
            $config['insightly']['api_key']
        );
    }

    public function testContactIntegration()
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'john.doe@anonymous.be');

        $createdContactId = $this->insightlyClient->createContact($contact)->getId();

        $insightlyContact = $this->insightlyClient->getContact($createdContactId);
        $this->assertEquals('John', $insightlyContact->getFirstName());
        $this->assertEquals('Doe', $insightlyContact->getLastName());

        $deleted = $this->insightlyClient->deleteContact($createdContactId);
        $this->assertTrue($deleted);
    }

    public function testOrganisationIntegration()
    {
        $organisation = new Organisation();
        $organisation->setName('Organisation Anonymous');

        $address = new Address();
        $address->setType('WORK');
        $address->setStreet('Street Name');
        $address->setCity('City Name');
        $address->setPostal('1000');
        $organisation->getAddresses()->append($address);

        $createdOrganisationId = $this->insightlyClient->createOrganisation($organisation)->getId();

        $insightlyOrganisation = $this->insightlyClient->getOrganisation($createdOrganisationId);
        $this->assertEquals('Organisation Anonymous', $insightlyOrganisation->getName());
        $this->assertEquals('Street Name', $insightlyOrganisation->getAddresses()->current()->getStreet());

        $deleted = $this->insightlyClient->deleteOrganisation($createdOrganisationId);
        $this->assertTrue($deleted);
    }

    public function testProjectIntegration()
    {
        $project = new Project();
        $project->setName('Project for John Doe');
        $project->setStatus(Project::STATUS_IN_PROGRESS);
        $project->setCategoryId(4345629);
        $project->setDetails('This project is created for John Doe');

        $createdProjectId = $this->insightlyClient->createProject($project)->getId();

        $insightlyProject = $this->insightlyClient->getProject($createdProjectId);
        $this->assertEquals('Project for John Doe', $insightlyProject->getName());
        $this->assertEquals(Project::STATUS_IN_PROGRESS, $insightlyProject->getStatus());
        $this->assertEquals(4345629, $insightlyProject->getCategoryId());
        $this->assertEquals('This project is created for John Doe', $insightlyProject->getDetails());

        $deleted = $this->insightlyClient->deleteProject($createdProjectId);
        $this->assertTrue($deleted);
    }

    public function testProjectWithLinksIntegration()
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'john.doe@anonymous.be');
        $createdContactId = $this->insightlyClient->createContact($contact)->getId();

        $organisation = new Organisation();
        $organisation->setName('Organisation Anonymous');
        $createdOrganisationId = $this->insightlyClient->createOrganisation($organisation)->getId();

        $project = new Project();
        $project->setName('Project for John Doe with Link');
        $project->setStatus(Project::STATUS_IN_PROGRESS);
        $project->setCategoryId(4345629);
        $project->setDetails('This project is created for John Doe with a link');

        $contactLink = new Link();
        $contactLink->setContactId($createdContactId);
        $contactLink->setRole('Aanvrager');
        $project->addLink($contactLink);

        $organizationLink = new Link();
        $organizationLink->setOrganisationId($createdOrganisationId);
        $project->addLink($organizationLink);

        $createdProjectId = $this->insightlyClient->createProject($project)->getId();

        $insightlyProject = $this->insightlyClient->getProject($createdProjectId);
        $this->assertEquals('Project for John Doe with Link', $insightlyProject->getName());
        $this->assertEquals(Project::STATUS_IN_PROGRESS, $insightlyProject->getStatus());
        $this->assertEquals(4345629, $insightlyProject->getCategoryId());
        $this->assertEquals('This project is created for John Doe with a link', $insightlyProject->getDetails());

        $insightlyLinks = $this->insightlyClient->getProjectLinks($createdProjectId);
        $this->assertEquals($createdContactId, $insightlyLinks[0]->getContactId());
        $this->assertEquals($createdOrganisationId, $insightlyLinks[1]->getOrganisationId());

        $deleted = $this->insightlyClient->deleteContact($createdContactId);
        $this->assertTrue($deleted);

        $deleted = $this->insightlyClient->deleteOrganisation($createdOrganisationId);
        $this->assertTrue($deleted);

        $deleted = $this->insightlyClient->deleteProject($createdProjectId);
        $this->assertTrue($deleted);
    }
}
