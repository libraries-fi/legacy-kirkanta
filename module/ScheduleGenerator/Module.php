<?php

namespace ScheduleGenerator;

use Kirkanta\Event\FormEvent;
use Kirkanta\Entity\Role;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $sm = $event->getApplication()->getServiceManager();
        $config = $sm->get('Config')['indexing'];

        if ($config['enabled']) {
            $events = $sm->get('Doctrine\ORM\EntityManager')->getEventManager();
            $events->addEventSubscriber(new Subscriber\GenerateSchedules($sm));
            $events->addEventSubscriber(new Subscriber\GenerateMobileStops($sm));
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }
}
