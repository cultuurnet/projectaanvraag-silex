<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Email;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\TaxNumber;

final class CustomFieldSerializer
{
    private const CUSTOM_FIELD_INTEGRATION_TYPE = 'Product__c';
    private const CUSTOM_FIELD_COUPON = 'Coupon__c';
    public const CUSTOM_FIELD_EMAIL = 'Email_boekhouding__c';
    public const CUSTOM_FIELD_TAX_NUMBER = 'BTW_nummer__c';

    public function getIntegrationType(array $customFields): IntegrationType
    {
        return new IntegrationType($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_INTEGRATION_TYPE));
    }

    public function getCoupon(array $customFields): Coupon
    {
        return new Coupon($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_COUPON));
    }

    public function getEmail(array $customFields): Email
    {
        return new Email($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_EMAIL));
    }

    public function getTaxNumber(array $customFields): TaxNumber
    {
        return new TaxNumber($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_TAX_NUMBER));
    }

    public function integrationTypeToCustomField(IntegrationType $integrationType): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_INTEGRATION_TYPE, $integrationType->getValue());
    }

    public function couponToCustomField(Coupon $coupon): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_COUPON, $coupon->getValue());
    }

    public function emailToCustomField(Email $email): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_EMAIL, $email->getValue());
    }

    public function taxNumberToCustomField(TaxNumber $taxNumber): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_TAX_NUMBER, $taxNumber->getValue());
    }

    public function getCustomFieldValue(array $customFields, string $key): string
    {
        foreach ($customFields as $customField) {
            if ($customField['CUSTOM_FIELD_ID'] === $key) {
                return $customField['FIELD_VALUE'];
            }
        }

        throw CustomFieldNotFound::forKey($key);
    }

    public function createCustomField(string $key, string $value): array
    {
        return [
            'FIELD_NAME' => $key,
            'CUSTOM_FIELD_ID' => $key,
            'FIELD_VALUE' => $value,
        ];
    }
}
