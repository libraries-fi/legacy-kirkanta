<?php

namespace KirkantaIndexing\Authentication;

use BjyAuthorize\Exception\UnAuthorizedException;
use BjyAuthorize\Guard\GuardInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class SecretKeyGuard implements GuardInterface
{
    const ERROR = 'error-invalid-secret';

    public function __construct($rules, ServiceLocatorInterface $service_manager)
    {
        $this->rules = $rules += ['routes' => []];
        $this->secret = $service_manager->get('Config')['indexing']['auth_key'];
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

        if (in_array($route, $this->rules['routes'])) {
            $key = $event->getRequest()->getQuery('auth');

            if ($key != $this->secret) {
                $event->setError(static::ERROR);
                $event->setParam('route', $route);
                $event->setParam('exception', new UnAuthorizedException('You are not authorized to access ' . $route));
            }
        }
    }
}
