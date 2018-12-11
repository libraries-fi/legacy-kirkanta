<?php

namespace Kirkanta\Authentication\Guard;

use BjyAuthorize\Exception\UnAuthorizedException;
use BjyAuthorize\Guard\GuardInterface;
use BjyAuthorize\Provider\Rule\ProviderInterface as RuleProviderInterface;
use Interop\Container\ContainerInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;

use Kirkanta\Entity\Role;
use Kirkanta\Entity\User;

class EntityAction implements GuardInterface
{
    const ERROR = 'error-unauthorized-entity';

    protected $admin_role = 'admin';
    protected $entity_manager;
    protected $zend_auth;
    protected $listeners = [];

    public function __construct($rules, ContainerInterface $container)
    {
        $this->entity_manager = $container->get('Doctrine\ORM\EntityManager');
        $this->zend_auth = $container->get('Zend\Authentication\AuthenticationService');
        $this->bjy_auth = $container->get('BjyAuthorize\Service\Authorize');
        $this->config = $container->get('config')['entities'];
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onDispatch'), -1000);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function onDispatch(MvcEvent $event)
    {
        $entity_class = $event->getRouteMatch()->getParam('entity');
        $active_route = $event->getRouteMatch()->getMatchedRouteName();

        if ($entity_class && isset($this->config[$entity_class])) {
            $resource_id = 'entity.' . $this->config[$entity_class]['alias'];

            foreach ($this->config[$entity_class]['routes'] as $action => $route_name) {
                if ($active_route === $route_name) {
                    if (!$this->bjy_auth->isAllowed($resource_id, $action)) {
                        var_dump($resource_id);
                        $this->error($event);
                    }
                    return;
                }
            }
        }
    }

    protected function error(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        $route = $match->getMatchedRouteName();

        $event->setError(static::ERROR);
        $event->setParam('route', $route);
        $event->setParam('identity', $this->bjy_auth->getIdentity());
        $event->setParam('exception', new UnAuthorizedException('You are not authorized to access this resource'));

        $event->getTarget()->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
    }

}
