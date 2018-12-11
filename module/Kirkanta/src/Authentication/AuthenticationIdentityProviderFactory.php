<?php

namespace Kirkanta\Authentication;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationIdentityProviderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $user                   = $serviceLocator->get('zfcuser_user_service');
        $simpleIdentityProvider = new AuthenticationIdentityProvider($user->getAuthService());
        $config                 = $serviceLocator->get('BjyAuthorize\Config');

        $simpleIdentityProvider->setDefaultRole($config['default_role']);
        $simpleIdentityProvider->setAuthenticatedRole($config['authenticated_role']);

        return $simpleIdentityProvider;
    }
}
