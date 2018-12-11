<?php

namespace Kirkanta\Navigation\Provider;

use Kirkanta\Entity\User;
use Zend\Mvc\Router\RouteMatch;

interface NavigationProviderInterface
{
    public function getContainer(RouteMatch $route_match, User $user, $entity = null);
}
