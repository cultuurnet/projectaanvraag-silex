<?php

namespace CultuurNet\UiTIDProvider\Security;

use CultuurNet\UiTIDProvider\User\UserServiceInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UiTIDAuthenticator implements AuthenticationProviderInterface
{
    /**
     * @var UserServiceInterface
     */
    protected $userService;

    /**
     * @param UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(TokenInterface $token)
    {
        $userId = $token->getUser();
        $user = $this->userService->getUser($userId);

        if (is_null($user)) {
            throw new AuthenticationException(sprintf('User with id %s does not exist.', $userId));
        }

        $token = new UiTIDToken($user->getRoles());
        $token->setUser($user);

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof UiTIDToken;
    }
}
