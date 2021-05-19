<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Integrations\Insightly\Serializers;

use CultuurNet\ProjectAanvraag\Integrations\Insightly\ValueObjects\Id;

final class LinkSerializer
{
    private const CONTACT_LINK_OBJECT_NAME = 'Contact';
    private const ORGANIZATION_LINK_OBJECT_NAME = 'Organization';

    public function contactIdToLink(Id $contactId): array
    {
        return [
            'LINK_OBJECT_ID' => $contactId->getValue(),
            'LINK_OBJECT_NAME' => self::CONTACT_LINK_OBJECT_NAME,
            'ROLE' => 'Aanvrager',
        ];
    }

    public function organizationIdToLink(Id $organizationId): array
    {
        return [
            'LINK_OBJECT_ID' => $organizationId->getValue(),
            'LINK_OBJECT_NAME' => self::ORGANIZATION_LINK_OBJECT_NAME,
        ];
    }

    public function contactIdFromLinks(array $links): Id
    {
        return $this->getId($links, self::CONTACT_LINK_OBJECT_NAME);
    }

    public function organizationIdFromLinks(array $links): Id
    {
        return $this->getId($links, self::ORGANIZATION_LINK_OBJECT_NAME);
    }

    private function getId(array $links, string $linkObjectName): Id
    {
        $id = null;

        foreach ($links as $link) {
            if ($link['LINK_OBJECT_NAME'] === $linkObjectName) {
                $id = new Id($link['LINK_OBJECT_ID']);
                break;
            }
        }

        return $id;
    }
}
