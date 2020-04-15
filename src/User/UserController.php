<?php

namespace CultuurNet\UiTIDProvider\User;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController
{
    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @var UserSessionServiceInterface
     */
    protected $userSessionService;

    /**
     * @param UserServiceInterface $userService
     * @param UserSessionServiceInterface $userSessionService
     */
    public function __construct(
        UserServiceInterface $userService,
        UserSessionServiceInterface $userSessionService
    ) {
        $this->userService = $userService;
        $this->userSessionService = $userSessionService;
    }

    /**
     * @return JsonResponse
     */
    public function getUser()
    {
        $user = null;
        $minimalUserInfo = $this->userSessionService->getMinimalUserInfo();

        if (is_null($minimalUserInfo)) {
            return new Response('No active user.', Response::HTTP_NOT_FOUND);
        }

        $user = $this->userService->getUser($minimalUserInfo->getId());

        return JsonResponse::create()
            ->setData($user)
            ->setPrivate();
    }

    /**
     * @return Response
     */
    public function logout()
    {
        $this->userSessionService->logout();
        return new Response();
    }
}
