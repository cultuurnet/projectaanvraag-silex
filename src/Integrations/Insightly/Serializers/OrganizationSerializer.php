<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Address;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\TaxNumber;

final class OrganizationSerializer
{
    private const CUSTOM_FIELD_EMAIL = 'Email_boekhouding__c';
    private const CUSTOM_FIELD_TAX_NUMBER = 'BTW_nummer__c';

    public function toInsightlyArray(Organization $organization): array
    {
        $organizationAsArray = [
            'ORGANISATION_NAME' => $organization->getName()->getValue(),
            'ADDRESS_BILLING_STREET' => $organization->getAddress()->getStreet(),
            'ADDRESS_BILLING_POSTCODE' => $organization->getAddress()->getPostal(),
            'ADDRESS_BILLING_CITY' => $organization->getAddress()->getCity(),
            'CUSTOMFIELDS' => [
                [
                    'FIELD_NAME' => self::CUSTOM_FIELD_EMAIL,
                    'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_EMAIL,
                    'FIELD_VALUE' => $organization->getEmail()->getValue(),
                ],
            ],
        ];

        if ($organization->getTaxNumber()) {
            $organizationAsArray[]['CUSTOMFIELDS'] = [
                'FIELD_NAME' => self::CUSTOM_FIELD_TAX_NUMBER,
                'CUSTOM_FIELD_ID' => self::CUSTOM_FIELD_TAX_NUMBER,
                'FIELD_VALUE' => $organization->getTaxNumber()->getValue(),
            ];
        }

        if ($organization->getId()) {
            $organizationAsArray['ORGANISATION_ID'] = $organization->getId()->getValue();
        }

        return $organizationAsArray;
    }

    public function fromInsightlyArray(array $insightlyArray): Organization
    {
        $email = null;
        $taxNumber = null;
        foreach ($insightlyArray['CUSTOMFIELDS'] as $customField) {
            if ($customField['CUSTOM_FIELD_ID'] === self::CUSTOM_FIELD_EMAIL) {
                $email = new Email($customField['FIELD_VALUE']);
            }

            if ($customField['CUSTOM_FIELD_ID'] === self::CUSTOM_FIELD_TAX_NUMBER) {
                $taxNumber = new TaxNumber($customField['FIELD_VALUE']);
            }
        }

        $organization = (new Organization(
            new Name($insightlyArray['ORGANISATION_NAME']),
            new Address(
                $insightlyArray['ADDRESS_BILLING_STREET'],
                $insightlyArray['ADDRESS_BILLING_POSTCODE'],
                $insightlyArray['ADDRESS_BILLING_CITY']
            ),
            $email
        ))->withId(new Id($insightlyArray['ORGANISATION_ID']));

        if ($taxNumber) {
            $organization = $organization->withTaxNumber($taxNumber);
        }

        return $organization;
    }
}
