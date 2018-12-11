<?php

namespace Kirkanta\Authentication;

use BjyAuthorize\Provider\Identity\AuthenticationIdentityProvider as BjyIdentityProvider;
use Kirkanta\Entity\Role;

class AuthenticationIdentityProvider extends BjyIdentityProvider
{
    public function getIdentityRoles()
    {
        $roles = parent::getIdentityRoles();
        $identity = $this->authService->getIdentity();
        if ($identity && !$identity->isAdministrator()) {
            $roles[] = $this->authenticatedRole;
        }
        return $roles;
    }
}
