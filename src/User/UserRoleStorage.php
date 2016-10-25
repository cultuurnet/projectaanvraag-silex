<?php

namespace CultuurNet\ProjectAanvraag\User;

use Symfony\Component\Yaml\Yaml;

class UserRoleStorage implements UserRoleStorageInterface
{
    /**
     * @var string
     */
    protected $configFilePath;

    /**
     * @var array
     */
    protected $userRoles;

    /**
     * UserRoleStorage constructor.
     * @param string $configFilePath
     */
    public function __construct($configFilePath)
    {
        $this->configFilePath = $configFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        if (!is_array($this->userRoles)) {
            $this->loadUserRoles();
        }

        return array_keys($this->userRoles);
    }

    /**
     * {@inheritdoc}
     */
    public function getRolesAndUserIds()
    {
        if (!is_array($this->userRoles)) {
            $this->loadUserRoles();
        }

        return $this->userRoles;
    }

    /**
     * Load the user roles from the YAML config file
     */
    private function loadUserRoles()
    {
        $this->userRoles = [];

        $roles = Yaml::parse(file_get_contents($this->configFilePath));
        if (is_array($roles) && !empty($roles['user_roles'])) {
            foreach ($roles['user_roles'] as $role => $ids) {
                !empty($this->userRoles[$role]) ? $this->userRoles[$role] += $ids : $this->userRoles[$role] = $ids;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRolesByUserId($userId)
    {
        $userRoles = [];

        foreach ($this->getRolesAndUserIds() as $role => $ids) {
            if (in_array($userId, $ids)) {
                $userRoles[] = $role;
            }
        }

        return $userRoles;
    }
}
