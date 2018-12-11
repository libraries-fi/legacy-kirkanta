<?php

namespace Kirkanta\Event\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;

abstract class AbstractListenerAggregate implements ListenerAggregateInterface
{
    protected $events = [];

    public function attach(EventManagerInterface $events)
    {
        foreach ($this->events as $params) {
            call_user_func_array([$events->getSharedManager(), 'attach'], $params);
        }
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->events as $params) {
            call_user_func_array([$events->getSharedManager(), 'detach'], $params);
        }
    }
}
