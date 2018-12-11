<?php

namespace Kirkanta\Navigation\Provider;

use Kirkanta\Entity\Organisation;
use Kirkanta\Entity\User;
use Zend\Mvc\Router\RouteMatch;

class MobileStopForm implements NavigationProviderInterface
{
    public function getContainer(RouteMatch $route_match, User $user, $entity = null)
    {
        $name = $route_match->getMatchedRouteName();
        $ok = strpos($name, 'organisation/') === 0
            && $name != 'organisation/add'
            && $entity instanceof Organisation
            && $entity->getType() === 'mobile_stop';

        if ($ok) {
            return 'mobile_stop_form_navigation';
        }
    }
}
