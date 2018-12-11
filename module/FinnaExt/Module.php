<?php

namespace FinnaExt;

use Kirkanta\Finna\Event\Listener\ConsortiumEdit;
use Kirkanta\Finna\Doctrine\Subscriber;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $event->getApplication()->getEventManager()->attach(new ConsortiumEdit);
        $services = $event->getApplication()->getServiceManager();
        $config = $services->get('Config');

        $subscribers = [
            new Subscriber\CreateConsortiumLinkGroups,
        ];

        if ($config['indexing']['enabled']) {
            $subscribers[] = Subscriber\ReactToSubresources::create($services);
        }

        $callable = [$services->get('Doctrine\ORM\EntityManager')->getEventManager(), 'addEventSubscriber'];
        array_map($callable, $subscribers);
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
                    'Kirkanta\Finna' => __DIR__ . '/src'
                ],
            ],
        ];
    }
}
