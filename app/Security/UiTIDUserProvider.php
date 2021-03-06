<?php

namespace CultuurNet\ProjectAanvraag\Security;

use CultuurNet\ProjectAanvraag\User\User;
use CultuurNet\UiTIDProvider\Security\UiTIDUserProvider as CultuurNetUiTIDUserProvider;

class UiTIDUserProvider extends CultuurNetUiTIDUserProvider
{
    public function supportsClass($class)
    {
        return $class === User::class;
    }
}
