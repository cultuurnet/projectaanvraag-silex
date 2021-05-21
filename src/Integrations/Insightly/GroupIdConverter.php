<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\IntegrationType;
use CultuurNet\ProjectAanvraag\IntegrationType\IntegrationTypeStorageInterface;
use InvalidArgumentException;

class GroupIdConverter
{
    /**
     * @var IntegrationTypeStorageInterface
     */
    private $integrationTypeStorage;

    public function __construct(IntegrationTypeStorageInterface $integrationTypeStorage)
    {
        $this->integrationTypeStorage = $integrationTypeStorage;
    }

    public function toIntegrationType(int $groupId): IntegrationType
    {
        $integrationType = $this->integrationTypeStorage->load($groupId);
        if (!$integrationType) {
            throw new InvalidArgumentException('No integration type found with group id ' . $groupId);
        }

        $insightlyIntegrationType = $integrationType->getInsightlyIntegrationType();
        if (!$insightlyIntegrationType) {
            throw new InvalidArgumentException(
                'The integration type with group id ' . $groupId . ' has no Insightly integration type configured'
            );
        }

        return $insightlyIntegrationType;
    }
}
