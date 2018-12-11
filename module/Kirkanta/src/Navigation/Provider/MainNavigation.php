<?php

namespace Kirkanta\Navigation\Provider;

use Kirkanta\Entity\User;
use Zend\Mvc\Router\RouteMatch;

class MainNavigation implements NavigationProviderInterface
{
    public function getContainer(RouteMatch $route_match, User $user, $entity = null)
    {
        if ($route_match->getMatchedRouteName() and !$user->isNew()) {
            return 'navigation';
        }
    }
}
