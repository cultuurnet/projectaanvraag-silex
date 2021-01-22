<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Address;
use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use CultuurNet\ProjectAanvraag\Insightly\Item\Organisation;
use CultuurNet\ProjectAanvraag\Insightly\Item\Project;
use Guzzle\Http\Client;
use Symfony\Component\Yaml\Yaml;

class InsightlyIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InsightlyClient
     */
    private $insighltyClient;

    /**
     * @var array
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->config = Yaml::parse(file_get_contents(__DIR__ . '/../../config.yml'));
        $this->insighltyClient = new InsightlyClient(
            new Client($this->config['insightly']['host']),
            $this->config['insightly']['api_key']
        );


    }

    public function testContactIntegration()
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doe');
        $contact->addContactInfo(ContactInfo::TYPE_EMAIL, 'john.doe@anonymous.be');

        $createdContactId = $this->insighltyClient->createContact($contact)->getId();

        $insightlyContact = $this->insighltyClient->getContact($createdContactId);
        $this->assertEquals('John', $insightlyContact->getFirstName());
        $this->assertEquals('Doe', $insightlyContact->getLastName());

        $deleted = $this->insighltyClient->deleteContact($createdContactId);
        $this->assertTrue($deleted);
    }

    public function testProjectIntegration()
    {
        $project = new Project();
        $project->setName('Project for John Doe');
        $project->setStatus(Project::STATUS_IN_PROGRESS);
        $project->setCategoryId(4345629);
        $project->setDetails('This project is created for John Doe');

        $createdProjectId = $this->insighltyClient->createProject($project)->getId();

        $insightlyProject = $this->insighltyClient->getProject($createdProjectId);
        $this->assertEquals('Project for John Doe', $insightlyProject->getName());
        $this->assertEquals(Project::STATUS_IN_PROGRESS, $insightlyProject->getStatus());
        $this->assertEquals(4345629, $insightlyProject->getCategoryId());
        $this->assertEquals('This project is created for John Doe', $insightlyProject->getDetails());

        $deleted = $this->insighltyClient->deleteProject($createdProjectId);
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

        $createdOrganisationId = $this->insighltyClient->createOrganisation($organisation)->getId();

        $insightlyOrganisation = $this->insighltyClient->getOrganisation($createdOrganisationId);
        $this->assertEquals('Organisation Anonymous', $insightlyOrganisation->getName());

        $deleted = $this->insighltyClient->deleteOrganisation($createdOrganisationId);
        $this->assertTrue($deleted);
    }
}
