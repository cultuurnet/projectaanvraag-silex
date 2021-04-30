<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\Exceptions\RecordNotFound;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\FirstName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\LastName;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class InsightlyClientTest extends TestCase
{
    /**
     * @var InsightlyClient
     */
    private $insightlyClient;

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
            $config['integrations']['insightly']['api_key']
        );
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

        $id = $this->insightlyClient->createContact($expectedContact);

        $actualContact = $this->insightlyClient->getContactById($id);
        $this->assertEquals(
            $expectedContact->withId($id),
            $actualContact
        );

        $this->insightlyClient->deleteContactById($id);

        $this->expectException(RecordNotFound::class);
        $this->insightlyClient->getContactById($id);
    }
}
