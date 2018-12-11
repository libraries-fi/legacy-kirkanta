<?php

namespace Kirkanta\Authentication;

use BjyAuthorize\Service\Authorize;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Extends the default service by feeding base authenticated role as parent to
 * every sub role.
 */
class AuthorizeService extends Authorize
{
    protected $guest_role;
    protected $authenticated_role;
    protected $standalone_roles = [];

    public static function create(ServiceLocatorInterface $sm)
    {
        return new static($sm->get('BjyAuthorize\Config'), $sm);
    }

    public function __construct(array $config, ServiceLocatorInterface $sm)
    {
        parent::__construct($config, $sm);
        $this->guest_role = $config['default_role'];
        $this->authenticated_role = $config['authenticated_role'];
        $this->standalone_roles = $config['standalone_roles'];
    }

    protected function addRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $standalone_roles = array_merge($this->standalone_roles, [
            $this->authenticated_role,
            $this->guest_role,
        ]);

        foreach ($roles as $role) {
            if ($this->acl->hasRole($role)) {
                continue;
            }

            if (!$this->acl->hasRole($role)) {
                if ($role->getParent()) {
                    $this->addRoles([$role->getParent()]);
                }
                if (in_array($role->getRoleId(), $standalone_roles, true)) {
                    $this->acl->addRole($role);
                } else {
                    $this->acl->addRole($role, [$role->getParent() ?: 'user']);
                }
            }
        }
    }
}
