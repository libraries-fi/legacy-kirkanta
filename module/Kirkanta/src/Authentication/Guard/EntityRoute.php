<?php

namespace Kirkanta\Authentication\Guard;

use BjyAuthorize\Exception\UnAuthorizedException;
use BjyAuthorize\Guard\GuardInterface;
use BjyAuthorize\Provider\Rule\ProviderInterface as RuleProviderInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;

use Kirkanta\Entity\Role;
use Kirkanta\Entity\User;

class EntityRoute implements GuardInterface
{
    const ERROR = 'error-unauthorized-entity';

    protected $admin_role = 'admin';
    protected $listeners = [];
    protected $service_locator;
    protected $entity_manager;
    protected $zend_auth;
    protected $rules;

    public function __construct($rules, ServiceLocatorInterface $service_locator)
    {
        $this->service_locator = $service_locator;
        $this->entity_manager = $this->service_locator->get('Doctrine\ORM\EntityManager');
        $this->zend_auth = $this->service_locator->get('Zend\Authentication\AuthenticationService');
        $this->bjy_auth = $this->service_locator->get('BjyAuthorize\Service\Authorize');
        $this->rules = $rules;

        $this->readRouteConfiguration($this->service_locator->get('Config')['entities']);
    }

    protected function readRouteConfiguration($config)
    {
        $accept_names = ['edit', 'delete'];
        foreach ($config as $entity) {
            foreach ($accept_names as $key) {
                if (!empty($entity['routes'][$key])) {
                    $this->rules['routes'][] = $entity['routes'][$key];
                }
            }
        }
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
        $match = $event->getRouteMatch();
        $route = $match->getMatchedRouteName();

        if (in_array($route, $this->rules['routes'])) {
            if (!$match->getParam('organisation_id') && !$match->getParam('id')) {
                return;
            }

            $entity_class = $match->getParam('entity');
            $entity_id = $match->getParam('organisation_id') ?: $match->getParam('id');
            $entity = $this->entity_manager->find($entity_class, $entity_id);
            $user = $this->zend_auth->getIdentity();
            $auth_id = $this->bjy_auth->getIdentity();

            if (!$user or !$entity) {
                $this->error($event);
            } elseif (method_exists($entity, 'getGroup') and !$this->userHasRole($user, $entity->getGroup())) {
                $this->error($event);
            }
        }
    }

    public function userHasRole(User $user, Role $role = null)
    {
        if (!$role) {
            $role = $this->entity_manager->getRepository(Role::class)->findOneBy(['role_id' => $this->admin_role]);
        }
        $roles = array_map(function($r) { return $r->getRoleId(); }, $user->getRoleTree());
        $isect = array_intersect($roles, [$role->getRoleId(), $this->admin_role]);
        return count($isect) > 0;
    }

    protected function error(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        $route = $match->getMatchedRouteName();

        $event->setError(static::ERROR);
        $event->setParam('route', $route);
        $event->setParam('identity', $this->bjy_auth->getIdentity());
        $event->setParam('exception', new UnAuthorizedException('You are not authorized to access ' . $route));

        /* @var $app \Zend\Mvc\Application */
        $app = $event->getTarget();
        $app->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);

    }

}
