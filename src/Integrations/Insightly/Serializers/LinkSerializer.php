<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;

final class LinkSerializer
{
    public function contactIdToLink(Id $contactId): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId->getValue(),
            'LINK_OBJECT_NAME' => 'Contact',
            'ROLE' => 'Aanvrager',
        ];
    }

    public function contactIdFromLinks(array $links): Id
    {
        $contactId = null;

        foreach ($links as $link) {
            if ($link['LINK_OBJECT_NAME'] === 'Contact') {
                $contactId = new Id($link['LINK_OBJECT_ID']);
                break;
            }
        }

        return $contactId;
    }
}
