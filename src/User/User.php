<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\User as UiTIDUser;

class User extends UiTIDUser implements UserInterface
{

    /**
     * The administrator role.
     */
    const USER_ROLE_ADMINISTRATOR = 'administrator';

    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @var bool
     */
    protected $isAdmin = false;

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
        $this->isAdmin = $this->hasRole(self::USER_ROLE_ADMINISTRATOR);
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Checks if the User has a given role
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * Check if the current user is admin.
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    public function jsonSerialize()
    {
        $json = [];

        foreach ($this as $key => $value) {
            if (!empty($value)) {
                $json[$key] = $value;
            }
        }

        // Unset the "following" property on the user, as it contains a recursive reference to the user
        // object itself, which makes it impossible to json_encode the user object.
        // @phpstan-ignore-next-line
        unset($json['following']);

        return $json;
    }

    /**
     * @param \CultureFeed_User $user
     * @return User|self
     */
    public static function fromCultureFeedUser(\CultureFeed_User $user)
    {
        $new = new self();

        $source = new \ReflectionObject($user);
        $properties = $source->getProperties();
        foreach ($properties as $propertyObject) {
            $property = $propertyObject->getName();
            $value = $propertyObject->getValue($user);

            $new->{$property} = $value;
        }

        return $new;
    }

    public static function fromPlatformUser(string $id, string $nick)
    {
        $new = new self();
        $new->id = $id;
        $new->nick = $nick;
        return $new;
    }
}
