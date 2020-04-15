<?php

namespace CultuurNet\UiTIDProvider\Security;

use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class PreflightRequestMatcher implements RequestMatcherInterface
{
    public function matches(Request $request)
    {
        return $this->isPreflightRequest($request);
    }

    /**
     * This will match any CORS preflight requests.
     * Borrowed form the silex-cors-provider.
     *
     * @param Request $request
     * @return bool
     */
    private function isPreflightRequest(Request $request)
    {
        return $request->getMethod() === "OPTIONS" && $request->headers->has("Access-Control-Request-Method");
    }
}
