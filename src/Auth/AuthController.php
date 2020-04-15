<?php

namespace CultuurNet\UiTIDProvider\Auth;

use CultuurNet\UiTIDProvider\User\UserSessionServiceInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController
{
    /**
     * Route name for the callback url for the OAuth request.
     */
    const AUTHORISATION_ROUTE_NAME = 'uitid.oauth.authorize';

    /**
     * @var AuthServiceInterface
     */
    protected $authService;

    /**
     * @var UserSessionServiceInterface
     */
    protected $userSessionService;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var string
     */
    protected $defaultDestination;

    /**
     * @param AuthServiceInterface $authService
     * @param UserSessionServiceInterface $userSessionService
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $defaultDestination
     */
    public function __construct(
        AuthServiceInterface $authService,
        UserSessionServiceInterface $userSessionService,
        UrlGeneratorInterface $urlGenerator,
        $defaultDestination = '/'
    ) {
        $this->authService = $authService;
        $this->userSessionService = $userSessionService;
        $this->urlGenerator = $urlGenerator;
        $this->defaultDestination = $defaultDestination;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function connect(Request $request)
    {
        $callback_url_params = array();
        if ($request->query->get('destination')) {
            $callback_url_params['destination'] = $request->query->get('destination');
        }

        if ($request->query->get('skipConfirmation')) {
            $callback_url_params['skipConfirmation'] = $request->query->get('skipConfirmation');
        }

        $callback_url = $this->urlGenerator->generate(
            self::AUTHORISATION_ROUTE_NAME,
            $callback_url_params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $token = $this->authService->getRequestToken($callback_url);
        $this->authService->storeRequestToken($token);

        $authorizeUrl = $this->authService->getAuthorizeUrl($token);
        return new RedirectResponse($authorizeUrl);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function authorize(Request $request)
    {
        $query = $request->query;
        $token = $this->authService->getStoredRequestToken();

        if ($query->get('oauth_token') == $token->getToken() && $query->get('oauth_verifier')) {
            $user = $this->authService->getAccessToken($token, $query->get('oauth_verifier'));

            $this->authService->removeStoredRequestToken();
            $this->userSessionService->setMinimalUserInfo($user);
        }

        if ($query->get('destination')) {
            return new RedirectResponse($query->get('destination'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate($this->defaultDestination));
        }
    }
}
