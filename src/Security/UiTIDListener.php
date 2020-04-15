<?php

namespace CultuurNet\UiTIDProvider\Security;

use CultuurNet\UiTIDProvider\User\UserSessionServiceInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class UiTIDListener implements ListenerInterface
{
    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var UserSessionServiceInterface
     */
    protected $userSessionService;

    /**
     * @param AuthenticationManagerInterface $authenticationManager
     * @param UserSessionServiceInterface $userSessionService
     */
    public function __construct(
        AuthenticationManagerInterface $authenticationManager,
        TokenStorageInterface $tokenStorage,
        UserSessionServiceInterface $userSessionService
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->tokenStorage = $tokenStorage;
        $this->userSessionService = $userSessionService;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $user = $this->userSessionService->getMinimalUserInfo();

        if (!is_null($user)) {
            $token = new UiTIDToken();
            $token->setUser((string) $user->getId());

            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);
                return;
            } catch (AuthenticationException $exception) {
            }
        }

        $response = new Response('Unauthorized access.', Response::HTTP_UNAUTHORIZED);
        $event->setResponse($response);
    }
}
