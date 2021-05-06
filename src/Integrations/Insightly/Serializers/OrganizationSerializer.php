<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;

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
}
