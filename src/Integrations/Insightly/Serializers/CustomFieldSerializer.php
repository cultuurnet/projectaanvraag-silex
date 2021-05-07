<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Coupon;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use InvalidArgumentException;

final class CustomFieldSerializer
{
    private const CUSTOM_FIELD_INTEGRATION_TYPE = 'Product__c';
    private const CUSTOM_FIELD_COUPON = 'Coupon_field__c';

    public function getIntegrationType(array $customFields): IntegrationType
    {
        return new IntegrationType($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_INTEGRATION_TYPE));
    }

    public function getCoupon(array $customFields): Coupon
    {
        return new Coupon($this->getCustomFieldValue($customFields, self::CUSTOM_FIELD_COUPON));
    }

    public function integrationTypeToCustomField(IntegrationType $integrationType): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_INTEGRATION_TYPE, $integrationType->getValue());
    }

    public function couponToCustomField(Coupon $coupon): array
    {
        return $this->createCustomField(self::CUSTOM_FIELD_COUPON, $coupon->getValue());
    }

    private function getCustomFieldValue(array $customFields, string $key): string
    {
        foreach ($customFields as $customField) {
            if ($customField['CUSTOM_FIELD_ID'] === $key) {
                return $customField['FIELD_VALUE'];
            }
        }

        throw new InvalidArgumentException('The key: ' . $key . ' is not found.');
    }

    private function createCustomField(string $key, string $value): array
    {
        return [
            'FIELD_NAME' => $key,
            'CUSTOM_FIELD_ID' => $key,
            'FIELD_VALUE' => $value,
        ];
    }
}
