<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Address;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Organization;
use InvalidArgumentException;

final class OrganizationSerializer
{
    /**
     * @var CustomFieldSerializer
     */
    private $customFieldSerializer;

    public function __construct()
    {
        $this->customFieldSerializer = new CustomFieldSerializer();
    }

    public function toInsightlyArray(Organization $organization): array
    {
        $organizationAsArray = [
            'ORGANISATION_NAME' => $organization->getName()->getValue(),
            'ADDRESS_BILLING_STREET' => $organization->getAddress()->getStreet(),
            'ADDRESS_BILLING_POSTCODE' => $organization->getAddress()->getPostal(),
            'ADDRESS_BILLING_CITY' => $organization->getAddress()->getCity(),
            'CUSTOMFIELDS' => [
                $this->customFieldSerializer->emailToCustomField($organization->getEmail()),
            ],
        ];

        if ($organization->getTaxNumber()) {
            $organizationAsArray['CUSTOMFIELDS'][] = $this->customFieldSerializer->taxNumberToCustomField(
                $organization->getTaxNumber()
            );
        }

        if ($organization->getId()) {
            $organizationAsArray['ORGANISATION_ID'] = $organization->getId()->getValue();
        }

        return $organizationAsArray;
    }

    public function fromInsightlyArray(array $insightlyArray): Organization
    {
        $organization = (new Organization(
            new Name($insightlyArray['ORGANISATION_NAME']),
            new Address(
                $insightlyArray['ADDRESS_BILLING_STREET'],
                $insightlyArray['ADDRESS_BILLING_POSTCODE'],
                $insightlyArray['ADDRESS_BILLING_CITY']
            ),
            $this->customFieldSerializer->getEmail($insightlyArray['CUSTOMFIELDS'])
        ))->withId(new Id($insightlyArray['ORGANISATION_ID']));

        try {
            $taxNumber = $this->customFieldSerializer->getTaxNumber($insightlyArray['CUSTOMFIELDS']);
            $organization = $organization->withTaxNumber($taxNumber);
        } catch (CustomFieldNotFound $customFieldNotFound) {
        }

        return $organization;
    }
}
