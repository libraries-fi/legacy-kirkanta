<?php

namespace Kirkanta\Navigation\Provider;

use Kirkanta\Entity\User;
use Zend\Mvc\Router\RouteMatch;

class OrganisationForm implements NavigationProviderInterface
{
    public function getContainer(RouteMatch $route_match, User $user, $entity = null)
    {
        $name = $route_match->getMatchedRouteName();
        if (strpos($name, 'organisation/') === 0 && $name != 'organisation/add') {
            return 'organisation_form_navigation';
        }
    }
}
