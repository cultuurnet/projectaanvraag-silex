<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Description;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Name;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Opportunity;
use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\OpportunityState;

final class OpportunitySerializer
{
    public function toInsightlyArray(Opportunity $opportunity): array
    {
        $opportunityAsArray = [
            'OPPORTUNITY_NAME' => $opportunity->getName()->getValue(),
            'OPPORTUNITY_STATE' => $opportunity->getState()->getValue(),
            'OPPORTUNITY_DETAILS' => $opportunity->getDescription()->getValue(),
        ];

        if ($opportunity->getId()) {
            $opportunityAsArray['OPPORTUNITY_ID'] = $opportunity->getId();
        }

        return $opportunityAsArray;
    }

    public function fromInsightlyArray(array $insightlyArray): Opportunity
    {
        return (new Opportunity(
            new Name($insightlyArray['OPPORTUNITY_NAME']),
            new OpportunityState($insightlyArray['OPPORTUNITY_STATE']),
            new Description($insightlyArray['OPPORTUNITY_DETAILS'])
        ))->withId(
            new Id($insightlyArray['OPPORTUNITY_ID'])
        );
    }
}
