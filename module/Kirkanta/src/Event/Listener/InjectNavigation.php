<?php

namespace Kirkanta\Event\Listener;

use Kirkanta\Navigation\Provider;
use Kirkanta\Entity\User;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Stdlib\DispatchableInterface;

class InjectNavigation extends AbstractListenerAggregate
{
    protected $providers;

    public function __construct()
    {
        $this->events = [
            [DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$this, 'injectNavigation']],
        ];

        $this->providers = [
            new Provider\MobileStopForm,
            new Provider\MobileLibraryForm,
            new Provider\OrganisationForm,
            new Provider\MainNavigation,
        ];
    }

    public function getContainer(RouteMatch $route_match, User $user, $entity)
    {
        foreach ($this->providers as $provider) {
            if ($container = $provider->getContainer($route_match, $user, $entity)) {
                return $container;
            }
        }
    }

    public function injectNavigation(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $user = $sm->get('Zend\Authentication\AuthenticationService')->getIdentity();
        $match = $event->getRouteMatch();

        if (!$user) {
            $user = new User;
        }

        if ($entity_class = $match->getParam('entity')) {
            $entity_info = $sm->get('Kirkanta\EntityPluginManager');
            $id_param = $entity_info->idParam($entity_class);
            if ($entity_id = $match->getParam($id_param)) {
                $entity = $sm->get('Doctrine\ORM\EntityManager')->find($entity_class, $entity_id);
            }
        }

        if (!isset($entity)) {
            $entity = null;
        }

        if ($user and $match) {
            $manager = $sm->get('ViewHelperManager');
            $acl = $sm->get('BjyAuthorize\Service\Authorize')->getAcl();
            $route = $match->getMatchedRouteName();

            if ($container = $this->getContainer($match, $user, $entity)) {
                $navigation = $manager->get('Navigation')->setContainer($container);
                $navigation->setAcl($acl)->setRole($user->getRole());
                $event->getViewModel()->setVariable('navigation', $navigation);
            }
        }
    }

}
