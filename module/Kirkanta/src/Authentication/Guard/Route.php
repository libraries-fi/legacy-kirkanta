<?php

namespace Kirkanta\Authentication\Guard;

use BjyAuthorize\Exception\UnAuthorizedException;
use BjyAuthorize\Guard\GuardInterface;
use BjyAuthorize\Provider\Rule\ProviderInterface as RuleProviderInterface;
use BjyAuthorize\Provider\Resource\ProviderInterface as ResourceProviderInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;

use Kirkanta\Entity\Role;
use Kirkanta\Entity\User;

class Route implements GuardInterface, ResourceProviderInterface, RuleProviderInterface
{
    const ERROR = 'error-unauthorized-route';
    const ROUTE_PREFIX = 'route:';

    protected $listeners = [];
    protected $service_locator;
    protected $auth_service;
    protected $rules = [];

    protected $route_match;

    public function __construct($rules, ServiceLocatorInterface $service_locator)
    {
        $this->service_locator = $service_locator;
        $this->entity_manager = $this->service_locator->get('Doctrine\ORM\EntityManager');
        $this->zend_auth = $this->service_locator->get('Zend\Authentication\AuthenticationService');
        $this->bjy_auth = $this->service_locator->get('BjyAuthorize\Service\Authorize');

        $config = $this->service_locator->get('Config');
        $defaults = $this->parseRoutes($config['router']['routes']);
        $rules = $this->mergeRules($defaults, $rules);
        $this->setRules($rules);
    }

    public function getResources()
    {
        return array_keys($this->rules);
    }

    public function getRules()
    {
        $rules = [];
        foreach ($this->rules as $resource => $roles) {
            $rules[] = [$roles, $resource];
        }
        return ['allow' => $rules];
    }

    protected function setRules(array $rules)
    {
        $this->rules = [];
        $this->addRules($rules);
    }

    protected function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $this->rules[static::ROUTE_PREFIX . $rule['route']] = $rule['roles'];
        }
    }

    protected function setRoutes($config)
    {
        $routes = $this->parseRoutes($config);
        $this->routes = $routes;
    }

    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], -1000);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function onRoute(MvcEvent $event)
    {
        $match = $event->getRouteMatch();
        $route = $match->getMatchedRouteName();
        if ($this->bjy_auth->isAllowed(static::ROUTE_PREFIX . $route)) {
            return;
        }
        $this->error($event);
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

    protected function getRolesForRoute($route)
    {
        if (!empty($this->routes[$route])) {
            return $this->routes[$route];
        } else {
            return [Role::ADMIN];
        }
    }

    protected function parseRoutes(array $config, $prefix = '', array $base_roles = [])
    {
        if (empty($base_roles)) {
            $base_roles = [Role::ADMIN];
        }
        $route_map = [];
        foreach ($config as $route => $config) {
            if (isset($config['options']['defaults']['_roles'])) {
                $roles = $config['options']['defaults']['_roles'];
            } else {
                $roles = $base_roles;
            }

            $route_map[$prefix . $route] = $roles;

            if (!empty($config['child_routes'])) {
                $sub_prefix = $prefix . $route . '/';
                $route_map = array_merge($route_map, $this->parseRoutes($config['child_routes'], $sub_prefix, $roles));
            }
        }

        return $route_map;
    }

    protected function mergeRules(array $route_map, array $config)
    {
        foreach ($config as $item) {
            $route_map[$item['route']] = $item['roles'];

            if (!empty($item['inherit'])) {
                foreach (array_keys($route_map) as $route) {
                    if (strpos($route, $item['route'] . '/') === 0) {
                        $route_map[$route] = $item['roles'];
                    }
                }
            }
        }
        foreach ($route_map as $route => $roles) {
            $rules[] = [
                'route' => $route,
                'roles' => $roles,
            ];
        }
        return $rules;
    }
}
