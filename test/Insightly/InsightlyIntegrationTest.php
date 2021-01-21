<?php declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Insightly;

use CultuurNet\ProjectAanvraag\Insightly\Item\Contact;
use CultuurNet\ProjectAanvraag\Insightly\Item\ContactInfo;
use Guzzle\Http\Client;
use Symfony\Component\Yaml\Yaml;

class InsightlyIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InsightlyClient
     */
    private $insighltyClient;

    protected function setUp()
    {
        parent::setUp();

        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../config.yml'));
        $this->insighltyClient = new InsightlyClient(
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

        $createdContactId = $this->insighltyClient->createContact($contact)->getId();

        $insightlyContact = $this->insighltyClient->getContact($createdContactId);
        $this->assertEquals('John', $insightlyContact->getFirstName());
        $this->assertEquals('Doe', $insightlyContact->getLastName());

        $deleted = $this->insighltyClient->deleteContact($createdContactId);
        $this->assertTrue($deleted);
        $this->assertNull($this->insighltyClient->getContact($createdContactId));
    }
}
