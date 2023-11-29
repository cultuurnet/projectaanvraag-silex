<?php

declare(strict_types=1);

namespace CultuurNet\ProjectAanvraag\Platform;

interface PlatformClientInterface
{
    public function hasAccessOnIntegration(string $integrationId): bool;

    public function validateToken(string $idToken): bool;

    public function getCurrentUser(): array;
}
