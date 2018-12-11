<?php

namespace KirkantaIndexing;

use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $services = $event->getApplication()->getServiceManager();
        $config = $services->get('Config');

        if ($config['indexing']['enabled']) {
            $em = $services->get('Doctrine\ORM\EntityManager');
            $em->getEventManager()->addEventSubscriber(new Subscriber\EntityIndexing($services));
            $em->getEventManager()->addEventSubscriber(new Subscriber\ReactToSubresources($services));
            $services->get('SharedEventManager')->attachAggregate(new Listener\OrganisationCleanups($services->get('Config')));
            $services->get('SharedEventManager')->attachAggregate(new Listener\ConsortiumCleanups($services->get('Config')));
            $services->get('SharedEventManager')->attachAggregate(Listener\PersonCleanups::create($services));
        }
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    'KirkantaIndexing' => __DIR__ . '/src',
                ],
            ],
        ];
    }
}
