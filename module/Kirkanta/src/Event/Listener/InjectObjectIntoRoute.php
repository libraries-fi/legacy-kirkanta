<?php

namespace Kirkanta\Event\Listener;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class InjectObjectIntoRoute extends AbstractListenerAggregate
{
    private $entities;

    public static function create(ContainerInterface $container)
    {
        return new static($container->get('Doctrine\ORM\EntityManager'));
    }

    public function __construct($entities)
    {
        $this->entities = $entities;

        $this->events = [
            [Application::class, MvcEvent::EVENT_ROUTE, [$this, 'injectObject']],
        ];
    }

    public function injectObject(MvcEvent $event)
    {
        $params = $event->getRouteMatch()->getParams();

        if (!empty($params['entity']) && !empty($params['id'])) {
            $container = $event->getApplication()->getServiceManager();
            $entities = $container->get('Doctrine\ORM\EntityManager');
            $object = $entities->find($params['entity'], $params['id']);
            $event->getRouteMatch()->setParam('object', $object);
        }

        if (!empty($params['entity']) && empty($params['id'])) {
            $event->getRouteMatch()->setParam('object', new $params['entity']);
        }
    }
}
