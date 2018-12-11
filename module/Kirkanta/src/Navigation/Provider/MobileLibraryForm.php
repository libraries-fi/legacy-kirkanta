<?php

namespace Kirkanta\Navigation\Provider;

use Kirkanta\Entity\User;
use Zend\Mvc\Router\RouteMatch;

class MobileLibraryForm implements NavigationProviderInterface
{
    public function getContainer(RouteMatch $route_match, User $user, $entity = null)
    {
        $name = $route_match->getMatchedRouteName();
        $ok = strpos($name, 'organisation/') === 0
            && $name != 'organisation/add'
            && $entity instanceof Organisation
            && $entity->getBranchType() === 'mobile_library';
        if ($ok) {
            return 'mobile_library_form_navigation';
        }
    }
}
