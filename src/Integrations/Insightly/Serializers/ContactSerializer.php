<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Contact;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\FirstName;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\LastName;

final class ContactSerializer
{
    public function toInsightlyArray(Contact $contact): array
    {
        $contactAsArray = [
            'FIRST_NAME' => $contact->getFirstName()->getValue(),
            'LAST_NAME' => $contact->getLastName()->getValue(),
            'EMAIL_ADDRESS' => $contact->getEmail()->getValue(),
        ];

        if ($contact->getId()) {
            $contactAsArray['CONTACT_ID'] = $contact->getId();
        }

        return $contactAsArray;
    }

    public function fromInsightlyArray(array $insightlyArray): Contact
    {
        return (new Contact(
            new FirstName($insightlyArray['FIRST_NAME']),
            new LastName($insightlyArray['LAST_NAME']),
            new Email($insightlyArray['EMAIL_ADDRESS'])
        ))->withId(
            new Id($insightlyArray['CONTACT_ID'])
        );
    }
}
