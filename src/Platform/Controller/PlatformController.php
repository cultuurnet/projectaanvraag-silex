<?php

namespace CultuurNet\ProjectAanvraag\Platform\Controller;

use CultuurNet\UiTIDProvider\User\UserSessionService;
use Guzzle\Http\Message\Response;
use Symfony\Component\HttpFoundation\Request;

class PlatformController
{
    /**
     * @var UserSessionService
     */
    private $userSessionService;
    public function __construct(UserSessionService $userSessionService)
    {
        $this->userSessionService = $userSessionService;
    }

    public function logout(Request $request): Response
    {
        $this->userSessionService->logout();
        return new Response(204);
    }
}
