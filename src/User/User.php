<?php

namespace CultuurNet\ProjectAanvraag\User;

use CultuurNet\UiTIDProvider\User\User as UiTIDUser;

class User extends UiTIDUser
{
    /**
     * @var array
     */
    protected $roles = [];

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
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
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $data = [];

        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }

        // Admin flag and roles
        $data['isAdmin'] = $this->hasRole('administrator');

        return $data;
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
}
